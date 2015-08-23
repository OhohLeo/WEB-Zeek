#!/usr/bin/perl
use strict;
use warnings;

use File::Basename;
use Getopt::Long;
use Net::FTP;
use Cwd;

use feature 'say';

my %AUTHORISED = map { $_ => 1 } qw(lib js css default projects extends img ace vendor);

my($action, $host, $login, $password, $directory, $force, $help);

GetOptions(
    'action|a=s'     => \$action,
    'host|h=s'       => \$host,
    'login|l=s'      => \$login,
    'password|p=s'   => \$password,
    'directory|d=s'  => \$directory,
    'force|f'        => \$force,
    'help|h'         => \$help);

sub display_help
{
    die <<END;
usage : deploy.pl -a [see|update|clean]
                  -h HOST NAME
                  -l LOGIN NAME
                  -p PASSWORD
                  -d DIRECTORY
                  -f FORCE
                  -h this help
END
}

display_help() if $help;

# action by default is update
$action //= 'update';
$directory //= cwd();
$host //= 'ftpperso.free.fr';

sub read_directory
{
    my($directory, $files) = @_;

    # we list the files to send
    my $filehandle;

    opendir($filehandle, $directory)
	or die "impossible to open '$directory' : $!";

    while (readdir($filehandle))
    {
	my $filename = $_;
	my $url = "$directory/$filename";

	if (-d $url and exists($AUTHORISED{$filename}))
	{
	    read_directory($url, $files);
	}
	elsif (-f $url and $filename =~ /\.(html|php|js|css|ini|png)+$/)
	{
	    next if $filename =~ /^(external_|internal_)+/;

	    $files->{$directory} //= [];
	    push(@{$files->{$directory}}, $filename);
	}
    }

    close($filehandle);
}

sub replace_link
{
    my($src, $dst) = @_;

    unlink($src);
    symlink($dst, $src);
}

my %files;

if ($force)
{
    read_directory($directory, \%files);
}
else
{
    my $pwd = `pwd`;
    chomp($pwd);

    my @list = split("\n", `git diff --name-only`);
    use Data::Dumper;

    foreach my $name (@list)
    {
        my($filename, $path) = fileparse($name);

        $path = substr($path, 0, -1);

        if (exists($AUTHORISED{$path}) || $path eq ".")
        {
            $path = "$pwd/$path";

            $files{$path} //= [];
            push($files{$path}, $filename);
        }
    }
}

if ($action eq 'see')
{
    use Data::Dumper;
    say Dumper \%files;
    exit;
}

if ($action eq 'clean')
{
}

unless ($action eq 'update')
{
    die "unknown action '$action'!\n";
}

unless (defined $login and defined $password and defined $host) {
    display_help();
}

# we handle specific behavior concerning the host
my($action_before, $action_after);

if ($host eq 'ftpperso.free.fr')
{
    my $filename = '.htaccess';

    $action_before = sub {
	# we create the file
	my $filehandle;
	open($filehandle, '>', "$directory/$filename");
	print {$filehandle} <<END;
php 1
END

	close($filehandle);

	# we add the file to the list
	push(@{$files{$directory}}, $filename);

	# we add the json to the list
	$files{"$directory/extends"} = [ 'json.php' ];

	# we use /var/config_free.ini file
	replace_link("$directory/config.ini",
		     "$directory/var/config_free.ini");

	# we add the sessions directory
	mkdir("$directory/sessions");

	# we add the sessions to the list
	$files{"$directory/sessions"} = [];
    };

    $action_after = sub {
	# we remove the file
	unlink("$directory/$filename");

	# we remove the sessions directory
	rmdir("$directory/sessions");

	# we use config file again
	replace_link("$directory/config.ini",
		     "$directory/var/config.ini");
    };
}

# we try to establish connection
my $ftp = Net::FTP->new($host, Passive => 1)
    or die "Cannot connect to '$host': $@";

# we try to login
$ftp->login($login, $password)
    or die "Cannot login " . $ftp->message;

say("connected to $host!");

# Place both servers into the correct transfer mode.
# In this case I'm using ASCII.
$ftp->ascii() or die  "Can't set ASCII mode: $!";

# we link the default script file to external
foreach my $replace (qw(scripts header))
{
    replace_link("$directory/default/$replace.php",
		 "$directory/default/external_$replace.php");
}

if (defined $action_before) {
    $action_before->();
}

my $size = length($directory);

while (my($cwd, $files) = each %files)
{
    $cwd = substr($cwd, $size);

    if ($cwd eq '')
    {
	$cwd = '/';
    }
    else
    {
	# we create the directory
	$ftp->mkdir($cwd, 1);
    }

    say "change directory '$cwd'";
    $ftp->cwd($cwd)
	or die "Cannot change working directory ", $ftp->message;

    # we write the file
    foreach my $file (@$files)
    {
	# we set binary mode
	$ftp->binary;

 	$ftp->put("$directory/$cwd/$file");
	say "send $file";
    }
}

# we link the default script file to internal
foreach my $replace (qw(scripts header))
{
    replace_link("$directory/default/$replace.php",
		 "$directory/default/internal_$replace.php");
}

if (defined $action_after) {
    $action_after->();
}

# we close connection
$ftp->close;
$ftp->quit;
