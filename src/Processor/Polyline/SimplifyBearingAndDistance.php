<?php

declare(strict_types=1);

namespace Location\Processor\Polyline;

use Location\Bearing\BearingEllipsoidal;
use Location\Distance\Vincenty;
use Location\GeometryInterface;
use Location\Polygon;
use Location\Polyline;
use Location\Processor\Polyline\SimplifyInterface;
use RuntimeException;

/**
 * Simplify Polyline considering bearing variations and max distance
 *
 * @author Marcus Jaschen <mjaschen@gmail.com>
 * modified by F Laporte <flap_152@yahoo.ca>
 */
class SimplifyBearingAndDistance implements SimplifyInterface
{
    /**
     * @var float
     */
    private $bearingAngle;
    private $distanceLimit;

    /**
     * SimplifyBearing constructor.
     *
     * @param float $bearingAngle
     */
    public function __construct(float $bearingAngle, int $distanceLimit)
    {
        $this->bearingAngle = $bearingAngle;
        $this->distanceLimit = $distanceLimit;
    }

    /**
     * @param Polyline $polyline
     *
     * @return Polyline
     * @throws RuntimeException
     */
    public function simplify(Polyline $polyline): Polyline
    {
        $result = $this->simplifyGeometry($polyline);

        if (!($result instanceof Polyline)) {
            throw new RuntimeException('Result is no Polyline', 4231694400);
        }

        return $result;
    }

    /**
     * Simplifies the given polyline
     *
     * 1. calculate the bearing angle between the first two points p1 and p2: b1
     * 2. calculate the bearing angle between the next two points p2 and p3: b2
     * 3. calculate the difference between b1 and b2: deltaB; if deltaB is
     *    smaller than the threshold angle, remove the middle point p2
     * 4. start again at (1.) as long as the polyline contains more points
     *
     * This method will be merged with `simplify()` in the next major release.
     *
     * @param GeometryInterface $geometry
     *
     * @return GeometryInterface
     */
    public function simplifyGeometry(GeometryInterface $geometry): GeometryInterface
    {
        if (!($geometry instanceof Polyline) && !($geometry instanceof Polygon)) {
            return $geometry;
        }

        $counterPoints = $geometry->getNumberOfPoints();

        if ($geometry instanceof Polygon) {
            if ($counterPoints <= 3) {
                return clone $geometry;
            }
            $result = new Polygon();
        } else {
            if ($counterPoints < 3) {
                return clone $geometry;
            }
            $result = new Polyline();
        }

        $bearingCalc = new BearingEllipsoidal();
        $distanceCalc = new Vincenty();

        $points = $geometry->getPoints();

        $index = 0;

        // add the first point to the resulting polyline
        $result->addPoint($points[$index]);
        $lastPointAddedIndex = $index;

        do {
            $index++;

            // preserve the last point of the original polyline
            if ($index === $counterPoints - 1) {
                $result->addPoint($points[$index]);
                $lastPointAddedIndex = $index;
                break;
            }

//            $distance = $distanceCalc->getDistance($points[$index - 1], $points[$index]);
            $distance = $distanceCalc->getDistance($points[$lastPointAddedIndex], $points[$index]);
            if ($distance > $this->distanceLimit) {
                $result->addPoint($points[$index]);
                $lastPointAddedIndex = $index;
                continue;
            }
            $bearing1 = $bearingCalc->calculateBearing($points[$index - 1], $points[$index]);
            $bearing2 = $bearingCalc->calculateBearing($points[$index], $points[$index + 1]);

            $bearingDifference = min(
                fmod($bearing1 - $bearing2 + 360, 360),
                fmod($bearing2 - $bearing1 + 360, 360)
            );

            $newSegmentLength = $distanceCalc->getDistance($points[$index], $points[$index + 1]);
            $distanceFromLast = $distanceCalc->getDistance($points[$lastPointAddedIndex], $points[$index ]) + $newSegmentLength;
            $distanceBoth = $distanceCalc->getDistance($points[$index - 1], $points[$index ]) + $newSegmentLength;
            if (
                $distanceBoth > min(5, $this->distanceLimit / 3)  && $bearingDifference > ($this->bearingAngle * 2) ||
                $distanceBoth > min(10, $this->distanceLimit / 3)  && $bearingDifference > $this->bearingAngle ||
                $distanceFromLast > min(10, $this->distanceLimit / 3)  && $bearingDifference > $this->bearingAngle
            ) {
                $result->addPoint($points[$index]);
                $lastPointAddedIndex = $index;
                continue;
            }
        } while ($index < $counterPoints);

        return $result;
    }
}
