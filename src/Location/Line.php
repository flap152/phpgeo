<?php
/**
 * Line Implementation
 *
 * PHP version 5
 *
 * @category  Location
 * @author    Marcus Jaschen <mjaschen@gmail.com>
 * @license   https://opensource.org/licenses/GPL-3.0 GPL
 * @link      https://github.com/mjaschen/phpgeo
 */

namespace Location;

use Location\Distance\DistanceInterface;

/**
 * Line Implementation
 *
 * @category Location
 * @author   Marcus Jaschen <mjaschen@gmail.com>
 * @license  https://opensource.org/licenses/GPL-3.0 GPL
 * @link     https://github.com/mjaschen/phpgeo
 */
class Line
{
    /**
     * @var \Location\Coordinate
     */
    protected $point1;

    /**
     * @var \Location\Coordinate
     */
    protected $point2;

    /**
     * @param Coordinate $point1
     * @param Coordinate $point2
     */
    public function __construct(Coordinate $point1, Coordinate $point2)
    {
        $this->point1 = $point1;
        $this->point2 = $point2;
    }

    /**
     * @param \Location\Coordinate $point1
     */
    public function setPoint1($point1)
    {
        $this->point1 = $point1;
    }

    /**
     * @return \Location\Coordinate
     */
    public function getPoint1()
    {
        return $this->point1;
    }

    /**
     * @param \Location\Coordinate $point2
     */
    public function setPoint2($point2)
    {
        $this->point2 = $point2;
    }

    /**
     * @return \Location\Coordinate
     */
    public function getPoint2()
    {
        return $this->point2;
    }

    /**
     * Calculates the length of the line (distance between the two
     * coordinates).
     *
     * @param DistanceInterface $calculator instance of distance calculation class
     *
     * @return float
     */
    public function getLength(DistanceInterface $calculator)
    {
        return $calculator->getDistance($this->point1, $this->point2);
    }

    /**
     * Create a new instance with reversed point order, i. e. reversed direction.
     *
     * @return Line
     */
    public function getReverse()
    {
        return new static($this->point2, $this->point1);
    }
}
