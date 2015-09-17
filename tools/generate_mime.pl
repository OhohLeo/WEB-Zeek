#
# Copyright (C) 2015  L�o Martin
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

use HTTP::Request;
use LWP::UserAgent;
use HTML::TokeParser;
use Data::Dumper;

# send the HTML request to get all the mime list
my $request = HTTP::Request->new(
    GET => 'http://www.sitepoint.com/web-foundations/mime-types-complete-list/');

my $ua = LWP::UserAgent->new;
my $response = $ua->request($request);

# parse to get the mime list
my %mime_list = ( '*/*' => {} );

my $parse = HTML::TokeParser->new(
    \$response->as_string());

my $extension;

while (my $token = $parse->get_token)
{
    my($tag, $attr, $attrseq, $rawtxt) = @$token;

    if($tag eq "S"
       and $attr eq "td"
       and exists $attrseq->{"headers"}
       and $attrseq->{"headers"} eq "d8073e47 d8073e50 ")
    {
        if (defined $extension)
        {
            my $type = $parse->get_trimmed_text;

            $mime_list{'*/*'}{$extension} = 1;

            if ($type =~ /([^\/]+)\/(.*)\z/)
            {
                my $generic_type = "$1/*";
                $mime_list{$generic_type} //= {};
                $mime_list{$generic_type}{$extension} = 1;
            }

            $mime_list{$type} //= {};

            $mime_list{$type}{$extension} = 1;
            undef $extension;
        }
        else
        {
            $extension = "'" . substr($parse->get_trimmed_text, 1) . "'";
        }
    }
}

my $mime_list;

while (my($type, $extensions) = each %mime_list)
{
    $mime_list .= "\n        '$type' => array("
        . join(',', keys $extensions) . "),";
}

chomp($mime_list);

# write the php mime class list
my $filename = 'lib/mime.php';
open(my $fh, '>', $filename)
    or die "Could not open file '$filename' $!";
my $result = "<?php
// File generated by tools/generate_mime.pl script
// based on www.sitepoint.com/web-foundations/mime-types-complete-list
class ZeekMime {

    private \$mime_list = array($mime_list);

    // Returns true if the mime does exist
    public function validate_mime_type(\$input) {
        return array_key_exists(\$input, \$this->mime_list);
    }
}

?>";

print {$fh} $result;
close $fh;
