<?php

require '../Include/Config.php';
require '../Include/Functions.php';

use ChurchCRM\dto\SystemConfig;
use ChurchCRM\Utils\InputUtils;

header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-Type: text/csv;charset='.SystemConfig::getValue("sCSVExportCharset"));
header('Content-Disposition: attachment; filename=SundaySchool-'.date(SystemConfig::getValue("sDateFilenameFormat")).'.csv');
header('Content-Transfer-Encoding: binary');

$delimiter = SystemConfig::getValue("sCSVExportDelemiter");

$out = fopen('php://output', 'w');

//add BOM to fix UTF-8 in Excel 2016 but not under, so the problem is solved with the sCSVExportCharset variable
if (SystemConfig::getValue("sCSVExportCharset") == "UTF-8") {
    fputs($out, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
}

// Get all the groups
$sSQL = 'select grp.grp_Name sundayschoolClass, kid.per_ID kidId, kid.per_FirstName firstName, kid.per_LastName LastName, kid.per_BirthDay birthDay,  kid.per_BirthMonth birthMonth, kid.per_BirthYear birthYear, kid.per_CellPhone mobilePhone,
fam.fam_HomePhone homePhone,
dad.per_FirstName dadFirstName, dad.per_LastName dadLastName, dad.per_CellPhone dadCellPhone, dad.per_Email dadEmail,
mom.per_FirstName momFirstName, mom.per_LastName momLastName, mom.per_CellPhone momCellPhone, mom.per_Email momEmail,
fam.fam_Email famEmail, fam.fam_Address1 Address1, fam.fam_Address2 Address2, fam.fam_City city, fam.fam_State state, fam.fam_Zip zip

from person_per kid, family_fam fam
left Join person_per dad on fam.fam_id = dad.per_fam_id and dad.per_Gender = 1 and dad.per_fmr_ID = 1
left join person_per mom on fam.fam_id = mom.per_fam_id and mom.per_Gender = 2 and mom.per_fmr_ID = 2
,`group_grp` grp, `person2group2role_p2g2r` person_grp  

where kid.per_fam_id = fam.fam_ID and person_grp.p2g2r_rle_ID = 2 and
grp_Type = 4 and grp.grp_ID = person_grp.p2g2r_grp_ID  and person_grp.p2g2r_per_ID = kid.per_ID
order by grp.grp_Name, fam.fam_Name';
$rsKids = RunQuery($sSQL);


fputcsv($out, [InputUtils::translate_special_charset('Class'),
  InputUtils::translate_special_charset('First Name'),
  InputUtils::translate_special_charset('Last Name'),
  InputUtils::translate_special_charset('Birth Date'),
  InputUtils::translate_special_charset('Mobile'),
  InputUtils::translate_special_charset('Home Phone'),
  InputUtils::translate_special_charset('Home Address'),
  InputUtils::translate_special_charset('Dad Name'),
  InputUtils::translate_special_charset('Dad Mobile') ,
  InputUtils::translate_special_charset('Dad Email'),
  InputUtils::translate_special_charset('Mom Name'),
  InputUtils::translate_special_charset('Mom Mobile'),
  InputUtils::translate_special_charset('Mom Email') ], $delimiter);


while ($aRow = mysqli_fetch_array($rsKids)) {
    extract($aRow);
    $birthDate = '';
    if ($birthYear != '') {
        $birthDate = $birthDay.'/'.$birthMonth.'/'.$birthYear;
    }
    fputcsv($out, [
    InputUtils::translate_special_charset($sundayschoolClass),
    InputUtils::translate_special_charset($firstName),
    InputUtils::translate_special_charset($LastName),
     $birthDate, $mobilePhone, $homePhone,
    InputUtils::translate_special_charset($Address1).' '.InputUtils::translate_special_charset($Address2).' '.InputUtils::translate_special_charset($city).' '.InputUtils::translate_special_charset($state).' '.$zip,
    InputUtils::translate_special_charset($dadFirstName).' '.InputUtils::translate_special_charset($dadLastName), $dadCellPhone, $dadEmail,
    InputUtils::translate_special_charset($momFirstName).' '.InputUtils::translate_special_charset($momLastName), $momCellPhone, $momEmail, ], $delimiter);
}


fclose($out);
