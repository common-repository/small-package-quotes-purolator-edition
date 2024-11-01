<?php

class purolatorSmallMultiPackgingClass {
    /*
     * calLength = calLength + (length * Quantity)
     * cartLength = array of length from cart
     * 
     * calwidth = calLength + (width * Quantity)
     * cartWidth = array of width from cart
     * 
     * calheight = calheight + (height * Quantity)
     * cartHeight = array of Height from cart
     */

    public function calculatePkgSize($calLength, $cartLength, $calwidth, $cartWidth, $calheight, $cartHeight) {

        // shipment level dimensional weight 
        $iteration = array();
        $iteration[1] = ceil($calLength) * ceil(max($cartWidth)) * ceil(max($cartHeight));
        $iteration[2] = ceil(max($cartLength)) * ceil(max($cartWidth)) * ceil($calheight);
        $iteration[3] = ceil(max($cartLength)) * ceil($calwidth) * ceil(max($cartHeight));
        // Get minimum dimension
        $dimensions = min($iteration);
        $min_iteration = array_keys($iteration, $dimensions);
        $min_iteration = $min_iteration[0];

        if ($min_iteration == 1) {
            $box_lenght = ceil(max($cartLength));
            $box_width = ceil(max($cartWidth));
            $box_height = ceil($calheight);
        }
        if ($min_iteration == 2) {
            $box_lenght = ceil($calLength);
            $box_width = ceil(max($cartWidth));
            $box_height = ceil(max($cartHeight));
        }
        if ($min_iteration == 3) {
            $box_lenght = ceil(max($cartLength));
            $box_width = ceil($calwidth);
            $box_height = ceil(max($cartHeight));
        }

        $diminsion_size = array($box_lenght, $box_width, $box_height);
        rsort($diminsion_size);
        $response['size'] = $diminsion_size[0] + ((2 * $diminsion_size[1]) + (2 * $diminsion_size[2]));
        $response['diminsion_size'] = $diminsion_size;
        return $response;
    }

    public function getMultiPkgByDimensions($request, $hittingCarrier = NULL) {

        $totalWeight = 0;
        $totalQubicFeet = 0;

        foreach ($request as $pI => $lineItem) {

            $totalLength = 0;
            $totalwidth = 0;
            $totalHeight = 0;
            $totalQuantity = 0;

            $totalLength = ($lineItem['length']) ? $lineItem['length'] : 0;
            $totalwidth = ($lineItem['width']) ? $lineItem['width'] : 0;
            $totalHeight = ($lineItem['height']) ? $lineItem['height'] : 0;
            $totalQuantity = ($lineItem['quantity']) ? $lineItem['quantity'] : 0;
            $totalWeight = $totalWeight + ($lineItem['weight'] * $totalQuantity);
            $totalQubicFeet = $totalQubicFeet + ($totalQuantity * $totalLength * $totalwidth * $totalHeight);
        }

        return self::clculateNumberOfPkgByDimensions($totalQubicFeet, $totalWeight, $hittingCarrier);
    }

    public function clculateNumberOfPkgByDimensions($totalQubicFeet, $totalWeight, $hittingCarrier = NULL) {

        (strlen(trim($hittingCarrier)) > 0 && $hittingCarrier == 'purolator') ? $sizeDivider = '216' : $sizeDivider = '165';
        $totalPkg = 1;
        while ($totalPkg <= 10) {

            $size = 0;
            $perQubic = 0;
            $qubicRoot = 0;
            $perQubic = $totalQubicFeet / $totalPkg;
            $qubicRoot = ceil(pow($perQubic, 1 / 3));
            $size = $qubicRoot + (2 * $qubicRoot) + (2 * $qubicRoot);

            if ($size <= $sizeDivider) {
                return array(
                    'calculatedNoOfPkg' => $totalPkg,
                    'calculatedSize' => $size,
                    'length' => $qubicRoot,
                    'width' => $qubicRoot,
                    'height' => $qubicRoot,
                    'weight' => $totalWeight,
                    'isLargePkg' => ($size > 130) ? 1 : 0,
                );
            }
            if ($totalPkg >= 10) {
                return array('error' => 'Package Limit has been exceed. max 10 packges are allowed');
            }
            $totalPkg ++;
        }
    }

    public function getMutlipkgesByWeight($request) {

        foreach ($request as $pI => $lineItem) {

            $totalLength = 0;
            $totalwidth = 0;
            $totalHeight = 0;
            $totalQuantity = 0;
            $totalWeight = 0;
            $totalQubicFeet = 0;

            $totalLength = ($lineItem['length']) ? $lineItem['length'] : 0;
            $totalwidth = ($lineItem['width']) ? $lineItem['width'] : 0;
            $totalHeight = ($lineItem['height']) ? $lineItem['height'] : 0;
            $totalQuantity = ($lineItem['quantity']) ? $lineItem['quantity'] : 0;
            $totalWeight = $totalWeight + ($lineItem['weight'] * $totalQuantity);
            $totalQubicFeet = $totalQubicFeet + ($totalQuantity * $totalLength * $totalwidth * $totalHeight);
        }

        $devideSmallPack = self::calculateTotalPackagesByWeight($totalWeight);
        $numberOfPackages = $devideSmallPack['pkgQuantity'];
        $weightPerPkg = $devideSmallPack['packageWeight'];
        if ($numberOfPackages > 10) {
            return array('error' => 'Package Limit has been exceed. max 10 packges are allowed');
        }

        $perQubic = $totalQubicFeet / $numberOfPackages;
        $qubicRoot = ceil(pow($perQubic, 1 / 3));
        $size = $qubicRoot + (2 * $qubicRoot) + (2 * $qubicRoot);

        $multiPkgArray = array(
            'calculatedNoOfPkg' => $numberOfPackages,
            'calculatedSize' => $size,
            'length' => $qubicRoot,
            'width' => $qubicRoot,
            'height' => $qubicRoot,
            'perPkgWeight' => $weightPerPkg,
            'TotalWeight' => $totalWeight,
            'isLargePkg' => ($size > 130) ? 1 : 0,
        );

        return $multiPkgArray;
    }

    public function calculateTotalPackagesByWeight($totalWeight) {

        $multiPkgsResponse = array();
        $pkgQuantity = ceil($totalWeight / 150);
        $multiPkgsResponse['pkgQuantity'] = $pkgQuantity;
        $packageWeight = $totalWeight / $pkgQuantity;
        $multiPkgsResponse['packageWeight'] = number_format((float) $packageWeight, 2, '.', '');
        return $multiPkgsResponse;
    }

}
