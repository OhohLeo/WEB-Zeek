#!/usr/bin/perl
use strict;
use warnings;

use Getopt::Long;
use Net::FTP;
use Cwd;

use feature 'say';

my %AUTHORISED = map { $_ => 1 } qw(lib js css view _partials);

my($action, $host, $login, $password, $directory, $help);

GetOptions(
    'action|a=s'     => \$action,
    'host|h=s'       => \$host,
    'login|l=s'      => \$login,
    'password|p=s'   => \$password,
    'directory|d=s'  => \$directory,
    'help|h'         => \$help);

if ($help)
{
    die <<END;
usage : deploy.pl -a [see|update|clean]
                  -h HOST NAME
                  -l LOGIN NAME
                  -p PASSWORD
                  -d DIRECTORY
                  -h this help
END
}

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
	elsif (-f $url and $filename =~ /\.(html|php|js|css|ini)+$/)
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
read_directory($directory, \%files);

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
    die "login, password & host should be defined for update"
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
	print {$filehandle} "php 1\n";
	close($filehandle);

	# we add the file to the list
	push($files{$directory}, $filename);

	# we add the json to the list
	$files{"$directory/extends"} = [ 'json.php' ];

	# we use /var/config_free.ini file
	replace_link("$directory/config.ini",
		     "$directory/var/config_free.ini");
    };

    $action_after = sub {
	# we remove the file
	unlink("$directory/$filename");

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

# we link the _partials script file to external
foreach my $replace (qw(scripts header))
{
    replace_link("$directory/_partials/$replace.php",
		 "$directory/_partials/external_$replace.php");
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
 	$ftp->put("$directory/$cwd/$file");
	say "send $file";
    }
}

# we link the _partials script file to internal
foreach my $replace (qw(scripts header))
{
    replace_link("$directory/_partials/$replace.php",
		 "$directory/_partials/internal_$replace.php");
}

if (defined $action_after) {
    $action_after->();
}

# we close connection
$ftp->close;
$ftp->quit;