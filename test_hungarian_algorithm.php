<?php

require_once('HungarianAlgorithm.php');

$students = range(0,9);

$slotsAvailability = [
    '10:00' => 2,
    '10:15' => 6,
    '10:30' => 3,
    '10:45' => 2,
    '11:00' => 8,
    '11:15' => 5,
    '11:30' => 10,
    '11:45' => 2,
    '12:00' => 10,
    '12:15' => 20,
    '12:30' => 2,
    '12:45' => 8,
];

$slotsAvailabiltyExpanded = [];
foreach ($slotsAvailability as $key => $item) {
    for($i = 0; $i < $item; $i++) {
        $slotsAvailabiltyExpanded[] = $key;
    }
}

$studentPreferences = [];
$matrix = [];
// We are going to create a random student preferences array
// studentPreferences will be used for the combinational method
// matrix will be used for the Hungarian algorithm
foreach ($students as $key =>$student) {
    // Generate random top 3 for each student
    $randomPrefs = array_rand($slotsAvailability, 3);
    $studentPreferences[] = [
        $randomPrefs[0],
        $randomPrefs[1],
        $randomPrefs[2],
    ];

    $matrix[$key] = [];
    $j = 1;
    foreach ($randomPrefs as $randomPref) {
        foreach (array_keys($slotsAvailabiltyExpanded, $randomPref) as $id) {
            $matrix[$key][$id] = $j;
        }
        $j++;
    }
    $slotsAvIds = range(0, count($slotsAvailabiltyExpanded)-1);
    foreach ($slotsAvIds as $id) {
        if(!isset($matrix[$key][$id])) {
            $matrix[$key][$id] = 999;
        }
    }
}

// TEST COMBINATIONAL
//$start = microtime(true);
//$combinations = combinations($studentPreferences);
//foreach ($combinations as $combi) {
//    // Check if available
//    $combinationOk = true;
//    foreach ($slotsAvailability as $slot => $amount) {
//        $arrCountVal = array_count_values($combi);
//        if(isset($arrCountVal[$slot]) && $arrCountVal[$slot] > $amount) {
//            $combinationOk = false;
//            break;
//        }
//    }
//    if($combinationOk) {
//        $finalCombi = $combi;
//        break;
//    }
//}
//
//$end = microtime(true);
//echo($end-$start);
//echo '<pre>' . var_export($finalCombi, true) . '</pre>';



// TEST HUNGARIAN
$start = microtime(true);
$ha = new HungarianAlgorithm();
$ha->initData($matrix);
$result = $ha->runAlgorithm();

// Do the translation
$finalResult = [];
foreach ($result as $student => $assignmentArray) {
    $assignedId = array_search(1, $assignmentArray);
    $finalResult[$student] = $slotsAvailabiltyExpanded[$assignedId];
}
$end = microtime(true);
echo($end-$start);

echo '<pre>' . var_export($finalResult, true) . '</pre>';


// https://stackoverflow.com/questions/8567082/how-to-generate-in-php-all-combinations-of-items-in-multiple-arrays
function combinations($arrays, $i = 0) {
    if (!isset($arrays[$i])) {
        return array();
    }
    if ($i == count($arrays) - 1) {
        return $arrays[$i];
    }

    // get combinations from subsequent arrays
    $tmp = combinations($arrays, $i + 1);

    $result = array();

    // concat each array from tmp with each element from $arrays[$i]
    foreach ($arrays[$i] as $k => $v) {
        foreach ($tmp as $x => $t) {
            $result[] = is_array($t) ?
                array_merge(array($v), $t) :
                array($v, $t);
        }
    }

    return $result;
}