<?php

namespace App\Service\Rating;

class RatingMatrixVisualizer
{
    public function display($matrix, $normaliseProfileIds, $n, $t1)
    {
        function getCellColor($level)
        {
            $c = [
                0 => 'lightblue',
                1 => 'yellow',
                2 => 'aqua',
            ];

            return $c[$level];
        }

        echo '<!DOCTYPE HTML><body><table>';
        echo '<tr><td></td>';
        foreach ($normaliseProfileIds as $normaliseProfileId1) {
            echo '<td>';
            echo $normaliseProfileId1;
            echo '</td>';
        }
        echo '</tr>';
        echo '<tr>';
        foreach ($normaliseProfileIds as $normaliseProfileId1) {
            echo '<tr>';
            echo '<td>';
            echo $normaliseProfileId1;
            echo '</td>';
            foreach ($normaliseProfileIds as $normaliseProfileId2) {
                $is = isset($matrix->matrix[$normaliseProfileId1][$normaliseProfileId2]);
                echo '<td bgcolor="' . ($is ? getCellColor($matrix->matrix[$normaliseProfileId1][$normaliseProfileId2]->getLevel()) : 'white') . '">' . ($is ? round($matrix->matrix[$normaliseProfileId1][$normaliseProfileId2]->getRatio(), 2) : '');
                echo '&nbsp;</td>';
            }
            echo '</tr>';
        }
        echo '</table>';

        // --------------------------------------------------------

        echo '<br><br><br><br>';
        echo '<table border="1px">';
        echo '<th>';
        echo '<tr><td></td>';
        foreach ($normaliseProfileIds as $normaliseProfileId1) {
            echo '<td>';
            echo $normaliseProfileId1;
            echo '</td>';
        }
        echo '</tr>';

        echo '<tr>';
        foreach ($matrix->matrix as $id => $colls) {
            echo '<tr>';
            echo '<td bgcolor="' . (in_array($id, $normaliseProfileIds) ? 'lightgrey' : '') . '">';
            echo $id;
            echo '</td>';
            foreach ($normaliseProfileIds as $normaliseProfileId2) {
                $is = isset($colls[$normaliseProfileId2]);
                echo '<td bgcolor="' . ($is ? getCellColor($colls[$normaliseProfileId2]->getLevel()) : 'white') . '">' . ($is ? round($colls[$normaliseProfileId2]->getRatio(), 2) : '');
                echo '&nbsp;</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        echo '</body>';

        var_dump([
            '!has ratio' => $matrix->a,
            'has ratio' => $matrix->n,
            'invert' => $matrix->m,
            'invertT' => $matrix->inv,
            'adds' => $n,
            't' => microtime(1) - $t1,
            'mem' => memory_get_usage(1) / 1024 / 1024
        ]);

        die();
    }
}
