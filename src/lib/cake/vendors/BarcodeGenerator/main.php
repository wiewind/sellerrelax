<?php

include_once('src/BarcodeGenerator.php');
include_once('src/BarcodeGeneratorPNG.php');
include_once('src/BarcodeGeneratorSVG.php');
include_once('src/BarcodeGeneratorJPG.php');
include_once('src/BarcodeGeneratorHTML.php');


include_once('src/Exceptions/BarcodeException.php');
include_once('src/Exceptions/InvalidCharacterException.php');
include_once('src/Exceptions/InvalidCheckDigitException.php');
include_once('src/Exceptions/InvalidFormatException.php');
include_once('src/Exceptions/InvalidLengthException.php');
include_once('src/Exceptions/UnknownTypeException.php');