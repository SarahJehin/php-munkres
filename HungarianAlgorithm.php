<?php

class HungarianAlgorithm
{
    // Source: https://github.com/liqul/php-munkres/blob/master/munkres.php
    // + slightly modified for non-square matrixes

    // Class specific variables
    //cost matrix
    private $C = array();
    //mask matrix
    private $M = array();
    private $rowCover = array();
    private $colCover = array();
    private $C_orig = array();
    private $path = array();
    private $nrow = 0;
    private $ncol = 0;
    private $step = 1;
    private $path_row_0 = 0;
    private $path_col_0 = 0;
    private $path_count = 0;
    private $asgn = 0;
    private $debug = false;

    /**
     * @param non-associative array $aCostMatrix  (eg. 0 (worker) => [0 (job) => 2 (cost), 1 (job) => 1 (cost), 2 (job) => 3 (cost)])
     */
    public function initData($aCostMatrix){
        $iSize = count($aCostMatrix);
        $this->nrow = $iSize;
        $this->ncol = count($aCostMatrix[0]);
        $this->C = $aCostMatrix;
        $this->M = array();
        for($i = 0; $i < $iSize; $i++){
            $this->M[] = array_pad(array(), count($aCostMatrix[0]), 0);
        }
        $this->rowCover = array_pad(array(), $iSize, 0);
        $this->colCover = array_pad(array(), count($aCostMatrix[0]), 0);
        $this->C_orig = array();
        $this->path = array();
        $this->step = 1;
        $this->path_row_0 = 0;
        $this->path_col_0 = 0;
        $this->path_count = 0;
        $this->asgn = 0;
    }

    private function showCostMatrix(){
        echo 'Show cost matrix<br>';
        for($r = 0; $r < $this->nrow; $r++){
            $str = "";
            for($c = 0; $c < $this->ncol; $c++){
                $str .= $this->C[$r][$c] . ",";
            }
            $str = substr($str, 0, strlen($str) - 1) . "\n";
            echo $str . "<br>";
        }
    }

    private function showMaskMatrix(){
        echo 'Show mask matrix<br>';
        for($r = 0; $r < $this->nrow; $r++){
            $str = "";
            for($c = 0; $c < $this->ncol; $c++){
                $str .= $this->M[$r][$c] . ",";
            }
            $str = substr($str, 0, strlen($str) - 1) . "\n";
            echo $str . '<br>';
        }
    }

    public function runAlgorithm(){
        $done = false;
        while(!$done){
            if($this->debug){
                $this->showCostMatrix();
                $this->showMaskMatrix();
            }
            switch ($this->step) {
                case 1:
                    $this->step_one();
                    break;
                case 2:
                    $this->step_two();
                    break;
                case 3:
                    $this->step_three();
                    break;
                case 4:
                    $this->step_four();
                    break;
                case 5:
                    $this->step_five();
                    break;
                case 6:
                    $this->step_six();
                    break;
                case 7:
                    $this->step_seven();
                    $done = true;
                    break;
            }
        }
        return $this->M;
    }

    // Find the minimum in each row and subtract this minimum from each value in the row
    private function step_one()
    {
        $min_in_row = 10000;
        for($r = 0; $r < $this->nrow; $r++){
            $min_in_row = $this->C[$r][0];
            for($c = 0; $c < $this->ncol; $c++){
                if($this->C[$r][$c] < $min_in_row){
                    $min_in_row = $this->C[$r][$c];
                }
            }
            for($c = 0; $c < $this->ncol; $c++){
                $this->C[$r][$c] -= $min_in_row;
            }
        }
        $this->step = 2;
        if($this->debug){
            echo "-------step1:step2-------\n" . '<br>';
        }
    }

    // For each cell check if its value is 0 and it is not in a covered row / column
    // If true -> set cell assigned (1) (in the maskMatrix ($this->M)) + set row and column as covered (unusable)
    // Finally (after loop) set all covered rows / columns uncovered again
    private function step_two()
    {
        for($r = 0; $r < $this->nrow; $r++){
            for($c = 0; $c < $this->ncol; $c++){
                if($this->C[$r][$c] == 0 &&
                    $this->rowCover[$r] == 0 &&
                    $this->colCover[$c] == 0){
                    $this->M[$r][$c] = 1;
                    $this->rowCover[$r] = 1;
                    $this->colCover[$c] = 1;
                }
            }
        }
        for($r = 0; $r < $this->nrow; $r++){
            $this->rowCover[$r] = 0;
        }
        for($c = 0; $c < $this->ncol; $c++){
            $this->colCover[$c] = 0;
        }
        $this->step = 3;
        if($this->debug){
            echo "-------step2:step3-------\n" . '<br>';
        }
    }

    // For all cells that are assigned, the column will be set to covered
    // Take the sum of all the covered columns
    // If the sum is greater than or equal to the total amount of columns/rows -> go to step 7, if not -> go to step 4
    private function step_three()
    {
        $colcount = 0;
        for($r = 0; $r < $this->nrow; $r++){
            for($c = 0; $c < $this->ncol; $c++){
                if($this->M[$r][$c] == 1){
                    $this->colCover[$c] = 1;
                }
            }
        }
        for($c = 0; $c < $this->ncol; $c++){
            if($this->colCover[$c] == 1){
                $colcount++;
            }
        }
        if($colcount >= $this->ncol ||
            $colcount >= $this->nrow){
            $this->step = 7;
            if($this->debug){
                echo "-------step3:step7-------\n" . '<br>';
            }
        }
        else{
            $this->step = 4;
            if($this->debug){
                echo "-------step3:step4-------\n" . '<br>';
            }
        }
    }

    private function find_a_zero(&$row, &$col)
    {
        $r = 0;
        $c = 0;
        $done = false;
        $row = -1;
        $col = -1;
        while(!$done){
            $c = 0;
            while(true){
                if($this->C[$r][$c] == 0 &&
                    $this->rowCover[$r] == 0 &&
                    $this->colCover[$c] == 0){
                    $row = $r;
                    $col = $c;
                    $done = true;
                }
                $c++;
                if($c >= $this->ncol || $done){
                    break;
                }
            }
            $r++;
            if($r >= $this->nrow){
                $done = true;
            }
        }
    }

    private function star_in_row($row)
    {
        $tmp = false;
        for($c = 0; $c < $this->ncol; $c++){
            if($this->M[$row][$c] == 1){
                $tmp = true;
            }
        }
        return $tmp;
    }

    // Return the column for this row which contains an assigned zero (if no assigned zero, -1 will be returned)
    private function find_star_in_row($row)
    {
        $col = -1;
        for($c = 0; $c < $this->ncol; $c++){
            if($this->M[$row][$c] == 1){
                $col = $c;
            }
        }
        return $col;
    }

    // Find a zero
    // If not found go to step six
    // If found, check if it is assigned, if yes, repeat for next row, if not go to step 5
    private function step_four()
    {
        $row = -1;
        $col = -1;
        $done = false;
        while(!$done){
            $this->find_a_zero($row, $col);
            if($row == -1){
                $done = true;
                $this->step = 6;
                if($this->debug){
                    echo "-------step4:step6-------\n" . '<br>';
                }
            }else{
                $this->M[$row][$col] = 2;
                if($this->star_in_row($row)){
                    $col = $this->find_star_in_row($row);
                    $this->rowCover[$row] = 1;
                    $this->colCover[$col] = 0;
                }else{
                    $done = true;
                    $this->step = 5;
                    if($this->debug){
                        echo "-------step4:step5-------\n" . '<br>';
                    }
                    $this->path_row_0 = $row;
                    $this->path_col_0 = $col;
                }
            }
        }
    }

    private function find_star_in_col($c)
    {
        $r = -1;
        for($i = 0; $i < $this->nrow; $i++){
            if($this->M[$i][$c] == 1){
                $r = $i;
            }
        }
        return $r;
    }

    private function find_prime_in_row($r)
    {
        $c = -1;
        for($i = 0; $i < $this->ncol; $i++){
            if($this->M[$r][$i] == 2){
                $c = $i;
            }
        }
        return $c;
    }

    private function augment_path()
    {
        for($p = 0; $p < $this->path_count; $p++){
            if($this->M[$this->path[$p][0]][$this->path[$p][1]] == 1){
                $this->M[$this->path[$p][0]][$this->path[$p][1]] = 0;
            }
            else{
                $this->M[$this->path[$p][0]][$this->path[$p][1]] = 1;
            }
        }
    }

    private function clear_covers()
    {
        for($r = 0; $r < $this->nrow; $r++){
            $this->rowCover[$r] = 0;
        }
        for($c = 0; $c < $this->ncol; $c++){
            $this->colCover[$c] = 0;
        }
    }

    private function erase_primes()
    {
        for($r = 0; $r < $this->nrow; $r++){
            for($c = 0; $c < $this->ncol; $c++){
                if($this->M[$r][$c] == 2){
                    $this->M[$r][$c] = 0;
                }
            }
        }
    }

    private function step_five()
    {
        $done = false;
        $r = -1;
        $c = -1;
        $this->path_count = 1;
        $this->path[$this->path_count - 1][0] = $this->path_row_0;
        $this->path[$this->path_count - 1][1] = $this->path_col_0;
        while(!$done){
            $r = $this->find_star_in_col($this->path[$this->path_count - 1][1]);
            if($r > -1){
                $this->path_count++;
                $this->path[$this->path_count - 1][0] = $r;
                $this->path[$this->path_count - 1][1] = $this->path[$this->path_count - 2][1];
            }else{
                $done = true;
            }
            if(!$done){
                $c = $this->find_prime_in_row($this->path[$this->path_count - 1][0]);
                $this->path_count++;
                $this->path[$this->path_count - 1][0] = $this->path[$this->path_count - 2][0];
                $this->path[$this->path_count - 1][1] = $c;
            }
        }
        $this->augment_path();
        $this->clear_covers();
        $this->erase_primes();
        $this->step = 3;
        if($this->debug){
            echo "-------step5:step3-------\n" . '<br>';
        }
    }

    // Find smallest value over all uncovered cells
    private function find_smallest(&$minval)
    {
        for($r = 0; $r < $this->nrow; $r++){
            for($c = 0; $c < $this->ncol; $c++){
                if($this->rowCover[$r] == 0 &&
                    $this->colCover[$c] == 0){
                    if($minval > $this->C[$r][$c]){
                        $minval = $this->C[$r][$c];
                    }
                }
            }
        }
    }

    // Find smallest value over all uncovered cells
    // For each uncovered cells subtract this min_value
    // For each double covered cells (collisions), add this min_val
    // Repeat step 4
    private function step_six()
    {
        $minval = 1000000;
        $this->find_smallest($minval);
        for($r = 0; $r < $this->nrow; $r++){
            for($c = 0; $c < $this->ncol; $c++){
                if($this->rowCover[$r] == 1){
                    $this->C[$r][$c] += $minval;
                }
                if($this->colCover[$c] == 0){
                    $this->C[$r][$c] -= $minval;
                }
            }
        }
        $this->step = 4;
        if($this->debug){
            echo "-------step6:step4-------\n" . '<br>';
        }
    }

    private function step_seven()
    {
        if($this->debug){
            echo "\n\n-------Run Complete--------";
        }
    }
}