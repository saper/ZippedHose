<?php

require 'ZippedHose.php';

$list = array(
	'/usr/home/saper/public_html/20100115',
	'/usr/home/saper/public_html/404',
	'/usr/home/saper/public_html/dump',
	'/usr/home/saper/public_html/ExtraSettings.php',
	'/usr/home/saper/public_html/fail',
	'/usr/home/saper/public_html/gerrit-differ',
	'/usr/home/saper/public_html/geshi.diff',
	'/usr/home/saper/public_html/g.png',
	'/usr/home/saper/public_html/h.html',
	'/usr/home/saper/public_html/koalicja-spam.txt',
	'/usr/home/saper/public_html/linkparser',
	'/usr/home/saper/public_html/lovereq.log',
	'/usr/home/saper/public_html/main',
	'/usr/home/saper/public_html/mediawiki-1.15.1.tar.gz',
	'/usr/home/saper/public_html/mediawiki-1.15.5.tar.gz',
	'/usr/home/saper/public_html/mediawiki-1.18.1.tar.gz',
	'/usr/home/saper/public_html/moving-to-branch',
	'/usr/home/saper/public_html/mw115_1',
	'/usr/home/saper/public_html/mw115_5',
	'/usr/home/saper/public_html/mw118',
	'/usr/home/saper/public_html/mwdumper-1.16.jar',
	'/usr/home/saper/public_html/net',
	'/usr/home/saper/public_html/oldpg',
	'/usr/home/saper/public_html/opp',
	'/usr/home/saper/public_html/opp2015.png',
	'/usr/home/saper/public_html/pg',
	'/usr/home/saper/public_html/pg0',
	'/usr/home/saper/public_html/pg2',
	'/usr/home/saper/public_html/pi.php',
	'/usr/home/saper/public_html/Pollock_domena_publiczna.pdf',
	'/usr/home/saper/public_html/ssh_svn.log',
	'/usr/home/saper/public_html/system',
	'/usr/home/saper/public_html/test',
	'/usr/home/saper/public_html/testfile',
	'/usr/home/saper/public_html/testfile.php',
	'/usr/home/saper/public_html/trunk',
	'/usr/home/saper/public_html/wf.diff',
	'/usr/home/saper/public_html/widget',
	'/usr/home/saper/public_html/w.png',
	'/usr/home/saper/public_html/zipfile.php',
	'/usr/home/saper/public_html/zipfile.zip'
);

$offset = 0;
$centralsize = 0;

$statlist = statfiles($list);
$map = array();
$directory = array();

foreach($statlist as $fstat) {
	$lh = localheader($fstat, null);
	if ($lh !== null) {
		$mapentry = absolutemapentry($offset, $lh);
		$central = dirmapentry($centralsize,
			centralheader($fstat, $offset, null));

		array_push($map, $mapentry);
		$offset = advance($offset, $mapentry);

		$filedata = filemapentry($offset, $fstat);
		array_push($map, $filedata);
		$offset = advance($offset, $filedata);

		array_push($directory, $central);
		$centralsize = advance($centralsize, $central);
	}
}

$centraloffset = $offset;
array_push($directory, dirmapentry($centralsize,
	endcentral($centraloffset, $centralsize, count($statlist))));

foreach($directory as $direntry) {
	array_push($map, pin($direntry, $centraloffset));
}
$DUMPFILE = 1;
if (isset($DUMPMAP))
	array_walk($map, "dump"); 
if (isset($DUMPFILE))
	array_walk($map, "push"); 
