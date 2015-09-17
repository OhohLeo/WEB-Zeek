#
# Copyright (C) 2015  Léo Martin
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#

#!/usr/bin/perl
use strict;
use warnings;

use File::Path qw(make_path remove_tree);
use File::Basename;
use File::Copy;

use Getopt::Long;
use Net::FTP;
use Cwd;

use CSS::Minifier;
use JavaScript::Minifier;

use feature 'say';

my %FORBIDDEN = map { $_ => 1 } qw(.. projects t tools var build);

my($action, $host, $login, $password, $directory, @input, $output, $force, $help);

GetOptions(
    'action|a=s'     => \$action,
    'host|t=s'       => \$host,
    'login|l=s'      => \$login,
    'password|p=s'   => \$password,
    'input|i=s'      => \@input,
    'output|o=s'     => \$output,
    'force|f'        => \$force,
    'help|h'         => \$help);

sub display_help
{
    die <<END;
usage : deploy.pl {options}
                  -a [see|update|clean]
                  -t HOST NAME
                  -l LOGIN NAME
                  -p PASSWORD
                  -i INPUT [ directory / file ]
                  -o OUTPUT
                  -f FORCE
                  -h this help
END
}

display_help() if $help;

# action by default is update
$action //= 'build';
$directory //= cwd();

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

        # Remove hidden file & readme, license & md files
        next if (substr($filename, 0, 1) eq "."
                 or lc($filename) ~~ [ qw(readme license) ]
                 or $filename =~ /\.(md|svg)$/i);

	if (-d $url and not exists($FORBIDDEN{$filename}))
	{
	    read_directory($url, $files);
	}
	elsif (-f $url)
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
        read_directory($directory . "/" . $input, \%files);
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

say "cleaning zeek";

my $dst = "$directory/build";

# Clean build directory
remove_tree $dst if -d $dst;

exit if $action eq 'clean';

say "building zeek";

# Create new directory
mkdir $dst;

while (my($src_directory, $src_files) = each %files)
{
    my $dst_directory =  $dst . substr($src_directory, length($directory));

    # Create all path
    make_path $dst_directory;

    # Copy all files
    foreach my $file (@$src_files)
    {
        if ($file =~ /\.(js|css)$/i)
        {
            my $ext = lc($1);

            my($in, $out);

            open($in, "$src_directory/$file") or die "Impossible to open $src_directory/$file";
            open($out, ">$dst_directory/$file") or die "Impossible to open $dst_directory/$file";

            if ($ext eq 'js')
            {
                JavaScript::Minifier::minify(input => $in, outfile => $out);
            }
            elsif ($ext eq 'css')
            {
                CSS::Minifier::minify(input => $in, outfile => $out);
            }

            close($in);
            close($out);
        }
        else
        {
            copy("$src_directory/$file", "$dst_directory/$file");
        }
    }
}


exit unless ($action eq 'update');

# unless (defined $login and defined $password and defined $host) {
#     display_help();
# }

# # we handle specific behavior concerning the host
# my($action_before, $action_after);

# if ($host eq 'ftpperso.free.fr')
# {
#     my $filename = '.htaccess';

#     $action_before = sub {
# 	# we create the file
# 	my $filehandle;
# 	open($filehandle, '>', "$directory/$filename");
# 	print {$filehandle} <<END;
# php56 1
# END

# 	close($filehandle);

# 	# we add the file to the list
# 	push(@{$files{$directory}}, $filename);

# 	# we add the json to the list
# 	$files{"$directory/extends"} = [ 'json.php' ];

# 	# we use /var/config_free.ini file
# 	replace_link("$directory/config.ini",
# 		     "$directory/var/config_free.ini");

# 	# we add the sessions directory
# 	mkdir("$directory/sessions");

# 	# we add the sessions to the list
# 	$files{"$directory/sessions"} = [];
#     };

#     $action_after = sub {
# 	# we remove the file
# 	unlink("$directory/$filename");

# 	# we remove the sessions directory
# 	rmdir("$directory/sessions");

# 	# we use config file again
# 	replace_link("$directory/config.ini",
# 		     "$directory/var/config.ini");
#     };
# }
# elsif ($host eq 'divebartheband.com')
# {
#     my $filename = '.htaccess';

#     $action_before = sub {
# 	# we create the file
# 	my $filehandle;
# 	open($filehandle, '>', "$directory/$filename");
# 	print {$filehandle} <<END;
# AddType x-mapp-php5.5 .php
# AddHandler x-mapp-php5.5 .php

# RewriteEngine on
# RewriteCond %{REQUEST_URI} !^/zeek
# RewriteRule ^(.*)$ /zeek/projects/1/DEPLOY/\$1 [L]

# END

#         chmod(0604, $filehandle);
# 	close($filehandle);

# 	# we add the file to the list
#         $files{"*$directory"} = [];
#         push(@{$files{"*$directory"}}, $filename);

# 	# we add the sessions directory
# 	mkdir("$directory/sessions");

# 	# we use /var/config_free.ini file
# 	replace_link("$directory/config.ini",
# 		     "$directory/var/config_1and1.ini");

# 	# we add the sessions to the list
# 	$files{"*$directory/sessions"} = [];
#     };

#     $action_after = sub {
# 	# we remove the file
# 	unlink("$directory/$filename");

# 	# we remove the sessions directory
# 	rmdir("$directory/sessions");

#         # we use config file again
# 	replace_link("$directory/config.ini",
# 		     "$directory/var/config.ini");
#     };
# }

# # we try to establish connection
# my $ftp = Net::FTP->new($host, Passive => 1)
#     or die "Cannot connect to '$host': $@";

# # we try to login
# $ftp->login($login, $password)
#     or die "Cannot login " . $ftp->message;

# say("connected to $host!");

# # Place both servers into the correct transfer mode.
# # In this case I'm using ASCII.
# $ftp->ascii() or die  "Can't set ASCII mode: $!";

# # we link the default script file to external
# foreach my $replace (qw(scripts header))
# {
#     replace_link("$directory/default/$replace.php",
# 		 "$directory/default/external_$replace.php");
# }

# if (defined $action_before) {
#     $action_before->();
# }

# my $size = length($directory);

# while (my($cwd, $files) = each %files)
# {
#     my $orig_dst;

#     if (substr($cwd, 0, 1) eq '*')
#     {
#         $cwd = substr($cwd, 1);
#         $orig_dst = 1;
#     }

#     my $relative_dst = substr($cwd, $size);
#     my $full_dst = $relative_dst;

#     if ($output && not $orig_dst)
#     {
#         $full_dst = "/$output$relative_dst";
#     }

#     if ($full_dst eq '')
#     {
# 	$full_dst = '/';
#     }
#     else
#     {
# 	# we create the directory
# 	$ftp->mkdir($full_dst, 1);
#     }

#     say "change directory '$full_dst'";
#     $ftp->cwd($full_dst)
# 	or die "Cannot change working directory ", $ftp->message;

#     # we write the file
#     foreach my $file (@$files)
#     {
#         # we set binary mode
#         $ftp->binary;
#         $ftp->put("$directory/$relative_dst/$file");
#         say "send $file";
#     }
# }

# # we link the default script file to internal
# foreach my $replace (qw(scripts header))
# {
#     replace_link("$directory/default/$replace.php",
# 		 "$directory/default/internal_$replace.php");
# }

# if (defined $action_after) {
#     $action_after->();
# }

# # we close connection
# $ftp->close;
# $ftp->quit;
