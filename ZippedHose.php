<?php

function statfiles($filelist) {
	return array_map(null, $filelist, array_map('stat', $filelist));
}

function absolutemapentry($offset, $content) {
	return array($offset, strlen($content), 0, $content);
}
function filemapentry($offset, $fstat) {
	$storedsize = is_directory($fstat) ? 0 :  $fstat[1]['size'];
	return array($offset, $storedsize, 1, $fstat);
}
function dirmapentry($offset, $content) {
	return array($offset, strlen($content), 2, $content);
}
function advance($offset, $mapentry) {
	return $offset + $mapentry[1];
}
function pin($dirmapentry, $offset) {
	return array($offset + $dirmapentry[0], $dirmapentry[1], 0, $dirmapentry[3]);
}
function push($mapentry) {
	switch ($mapentry[2]) {
	case 0:
	case 2:
		print $mapentry[3];
		break;
	case 1:
		print pack("x" . $mapentry[1]);
	}
}
function dump($mapentry) {
	printf("%08X %08X %08X ", $mapentry[0], $mapentry[0] + $mapentry[1], $mapentry[1]);
	switch ($mapentry[2]) {
	case 0:
	case 2:
		$a = unpack("V", substr($mapentry[3], 0, 4));
		printf("%08x", $a[1]);
		break;
	case 1:
		print $mapentry[3][0];
	}
	print "\n";
}

function is_directory($fstat) {
	return ($fstat[1]["mode"] & 0040000);
}
function coreheader($fstat, $extra) {
	if (($fstat[1]["mode"] & 0140000) === 0) { /* S_IFDIR | S_IFREG */
		return null;
	}
	if (is_directory($fstat)) {
		$type = 20;
	} else {
		$type = 10;
	}

	return pack("v5V3v2",
			$type,     /* 10 regular file, 20 for dir, 45 for zip64 */
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
	$ch = coreheader($fstat, $extra);
	if ($ch === null)
		return null;
	return pack("V", 0x04034b50) .
		$ch .
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
