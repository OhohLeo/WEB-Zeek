#!/usr/bin/perl
use strict;
use warnings;

use File::Basename;
use Getopt::Long;
use Net::FTP;
use Cwd;

use feature 'say';

my %FORBIDDEN = map { $_ => 1 } qw(.. DEPLOY projects t tools var);

my($action, $host, $login, $password, $directory, @input, $output, $force, $help);

GetOptions(
    'action|a=s'     => \$action,
    'host|t=s'       => \$host,
    'login|l=s'      => \$login,
    'password|p=s'   => \$password,
    'directory|d=s'  => \$directory,
    'input|i=s'      => \@input,
    'output|o=s'     => \$output,
    'force|f'        => \$force,
    'help|h'         => \$help);

sub display_help
{
    die <<END;
usage : deploy.pl {options} [ directory / file ]
                  -a [see|update|clean]
                  -t HOST NAME
                  -l LOGIN NAME
                  -p PASSWORD
                  -d DIRECTORY
                  -i INPUT
                  -o OUTPUT
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

	if (-d $url
            and not exists($FORBIDDEN{$filename})
            and substr($filename, 0, 1) ne ".")
	{
	    read_directory($url, $files);
	}
	elsif (-f $url) # and $filename =~ /\.(html|php|js|css|ini|png)+$/)
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

if (@input)
{
    foreach my $input (@input)
    {
        read_directory($input, \%files);
    }
}
elsif ($force)
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

        if (not exists($FORBIDDEN{$path})
            || substr($filename, 0, 1) eq ".")
        {
            $path = "$pwd/$path";

            $files{$path} //= [];
            push(@{$files{$path}}, $filename);
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
php56 1
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
elsif ($host eq 'divebartheband.com')
{
    my $filename = '.htaccess';

    $action_before = sub {
	# we create the file
	my $filehandle;
	open($filehandle, '>', "$directory/$filename");
	print {$filehandle} <<END;
AddType x-mapp-php5.5 .php
AddHandler x-mapp-php5.5 .php
END

	close($filehandle);

	# we add the file to the list
        $files{"*$directory"} = [];
        push(@{$files{"*$directory"}}, $filename);

	# we add the sessions directory
	mkdir("$directory/sessions");

	# we use /var/config_free.ini file
	replace_link("$directory/config.ini",
		     "$directory/var/config_1and1.ini");

	# we add the sessions to the list
	$files{"*$directory/sessions"} = [];
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
    my $orig_dst;

    if (substr($cwd, 0, 1) eq '*')
    {
        $cwd = substr($cwd, 1);
        $orig_dst = 1;
    }

    my $relative_dst = substr($cwd, $size);
    my $full_dst = $relative_dst;

    if ($output && not $orig_dst)
    {
        $full_dst = "/$output$relative_dst";
    }

    if ($full_dst eq '')
    {
	$full_dst = '/';
    }
    else
    {
	# we create the directory
	$ftp->mkdir($full_dst, 1);
    }

    say "change directory '$full_dst'";
    $ftp->cwd($full_dst)
	or die "Cannot change working directory ", $ftp->message;

    # we write the file
    foreach my $file (@$files)
    {
	# we set binary mode
	$ftp->binary;
 	$ftp->put("$directory/$relative_dst/$file");
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
