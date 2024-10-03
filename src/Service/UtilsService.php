<?php

namespace App\Service;

class UtilsService{
    public function isEmptyJson($json): bool
    {
        if($json == '""'){
            return true;
        }
        return false;
    }
}
