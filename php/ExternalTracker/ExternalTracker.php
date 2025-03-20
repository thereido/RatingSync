<?php
namespace RatingSync;

interface ExternalTracker
{

    static public function exportCsv(array $films): string;

}