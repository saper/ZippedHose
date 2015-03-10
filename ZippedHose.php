<?php

function statone($f) {
	 stat($f);
	$s['filename'] = $f;
	print_r($s);
	return $s;
}

function statfiles($filelist) {
	return array_map(null, $filelist, array_map('stat', $filelist));
}

function absolutemapentry($offset, $content) {
	return array($offset, strlen($content), 0, $content);
}
function filemapentry($offset, $fstat) {
	return array($offset, $fstat[1]['size'], 1, $fstat);
}
function dirmapentry($offset, $content) {
	return array($offset, strlen($content), 2, $content);
}
function advance($offset, $mapentry) {
	return $offset + $mapentry[1];
}
function push($mapentry) {
	switch ($mapentry[2]) {
	case 0:
	case 2:
		print $mapentry[3];
	}
}

function coreheader($fstat, $extra) {
	return pack("v5V3v2",
			10, /* 20 for dir, 45 for zip64 */
			0x0000,    /* special purpose */
			0x0000,    /* no compression */
			0x0000,    /* mod time  */
			0x0000,    /* mod date  */
			0x00000000,        /* CRC32 */
			0x00000000,        /* compressed size */
			0x00000000,        /* uncompressed size */
			strlen($fstat[0]), /* file name length */
			strlen($extra));   /* extra field length */
}

function localheader($fstat, $extra) {
	return pack("V", 0x04034b50) .
		coreheader($fstat, $extra) .
		$fstat[0] .
		$extra;
}

function centralheader($fstat, $offset, $extra) {
	return pack("Vv",
			0x02014b50,
			0x0000) . /* Version */
		coreheader($fstat, $extra) .
		pack("v3V2",
			0x0000,    /* file comment length */
			0x0000,    /* disk number start */
			0x0000,    /* internal file attributes */
			0x0000,    /* external file attributes */
			$offset) .      /* localheader relative offset */
		$fstat[0] .
		$extra;
}

function endcentral($centraloffset, $centralsize, $entries) {
	return pack("Vv4V2v",
			0x06054b50,
			0x0000,    /* number of this disk */
			0x0000,    /* disk where central starts */
			$entries,  /* number of disk with central */
			$entries,  /* number of entries */
			$centralsize,      /* size of the central directory */
			$centraloffset,    /* offset of start of central dir */
			0x0000);   /* ZIP comment length */
}
