<?php

/**
 * Finds the best groupment for a list of numbers
 *
 * ksets([100, 1, 2, 3, 4, 14, 15, 17, 90, 92, 97])
 * ksets([317, 319, 321, 323, 362, 363, 366, 367, 370, 374, 378, 380, 382, 535])
 * ksets([316, 358, 360, 361, 364, 368, 372, 374, 376, 527])
 *
 * @param array $list List of numbers
 * @param int $min Minimum distance allowed
 * @return array Groups
 * @author Alejandro Moraga <moraga86@gmail.com>
 */
function gn1(array $list, int $min = 0): array {
    $size = count($list);
    $opts = [];
    $uniq = [];
    $dmax = 0;
    $best = ['rank' => 0, 'grps' => [$list]];

    for ($prev = $list[0], $i = 1; $i < $size; ++$i) {
        $dist = $list[$i] - $prev;
        $prev = $list[$i];
        if ($dist > $min && !in_array($dist, $uniq))
            $uniq[] = $dist;
        if ($dmax < $dist)
            $dmax = $dist;
    }

    $list[] = $list[$size - 1] + $dmax + 1;

    foreach ($uniq as $maxd) {
        $opt = [
            'dist' => $maxd,
            'grps' => [],
            'gdis' => [],
            'edis' => [],
            'enum' => [],
            'rank' => 0,
        ];
        $gnum = 0;

        for (
            $temp = [$prev = $list[0]],
            $ante = null,
            $enum = 1,
            $sdis = 0,
            $i = 1; $i <= $size; ++$i) {
            $dist = $list[$i] - $prev;
            $prev = $list[$i];
            if ($dist <= $maxd) {
                $temp[] = $list[$i];
                $sdis += $dist;
                ++$enum;
            }
            else {
                $opt['grps'][] = $temp;
                $opt['edis'][] = $enum == 1 ? 0 : $sdis / ($enum - 1);
                $opt['enum'][] = $enum;
                if ($ante)
                    $opt['gdis'][] = $temp[0] - $ante[count($ante) - 1];
                ++$gnum;
                $ante = $temp;
                $temp = [$list[$i]];
                $enum = 1;
                $sdis = 0;
            }
        }

        if ($gnum > 1) {
            if ($gnum == 2)
                array_unshift($opt['gdis'], 0);

            //$gdmx = max($opt['gdis']);
            $gdmx = array_sum($opt['gdis']) / count($opt['gdis']);
            //$edmx = max($opt['edis']);
            $edmx = array_sum($opt['edis']) / $gnum;
            $enmx = max($opt['enum']);

            $opt['rank'] =
                // distance between groups
                (array_reduce($opt['gdis'], function($sum, $val) use($gdmx) {
                    return $sum + min($val, $gdmx) / max($val, $gdmx);
                }, 0) / count($opt['gdis'])) +
                // distance between elements in group
                (array_reduce($opt['edis'], function($sum, $val) use($edmx) {
                    return $sum + min($val, $edmx) / max($val, $edmx);
                }, 0) / $gnum) +
                // equality number of elements
                (array_reduce($opt['enum'], function($sum, $val) use($enmx) {
                    return $sum + $val / $enmx;
                }, 0) / $gnum);

            if ($best['rank'] < $opt['rank'])
                $best = $opt;
        }
    }

    return $best['grps'];
}

?>
