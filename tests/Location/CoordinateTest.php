<?php

namespace Location;

use Location\Coordinate;
use Location\Ellipsoid;
use Location\Distance\Vincenty;
use Location\Formatter\DecimalDegrees;

class CoordinateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ellipsoid
     */
    protected $ellipsoid;

    /**
     * @var Coordinate
     */
    protected $coordinate;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $ellipsoidConfig = array(
            'name' => 'WGS-84',
            'a'    => 6378137.0,
            'b'    => 6356752.3142,
            'f'    => 298.257223563,
        );

        $this->ellipsoid = Ellipsoid::createFromArray($ellipsoidConfig);

        $this->coordinate = new Coordinate(52.5, 13.5, $this->ellipsoid);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->ellipsoid);
        unset($this->coordinate);
    }

    /**
     * @covers Location\Coordinate::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorInvalidLatitude()
    {
        $c = new Coordinate('foo', 13.5, $this->ellipsoid);
    }

    /**
     * @covers Location\Coordinate::__construct
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorInvalidLongitude()
    {
        $c = new Coordinate(52.5, 'foo', $this->ellipsoid);
    }

    /**
     * @covers Location\Coordinate::__construct
     */
    public function testConstructorDefaultEllipsoid()
    {
        $c = new Coordinate(52.5, 13.5);
    }

    /**
     * @covers Location\Coordinate::getLat
     */
    public function testGetLat()
    {
        $this->assertEquals(52.5, $this->coordinate->getLat());
    }

    /**
     * @covers Location\Coordinate::getLng
     */
    public function testGetLng()
    {
        $this->assertEquals(13.5, $this->coordinate->getLng());
    }

    /**
     * @covers Location\Coordinate::getEllipsoid
     */
    public function testGetEllipsoid()
    {
        $this->assertEquals($this->ellipsoid, $this->coordinate->getEllipsoid());
    }

    /**
     * @covers Location\Coordinate::getDistance
     */
    public function testGetdistance()
    {
        $coordinate1 = new Coordinate(19.820664, -155.468066, $this->ellipsoid);
        $coordinate2 = new Coordinate(20.709722, -156.253333, $this->ellipsoid);

        $this->assertEquals(128130.850, $coordinate1->getDistance($coordinate2, new Vincenty()));
    }

    /**
     * @covers Location\Coordinate::format
     */
    public function testFormat()
    {
        $this->assertEquals("52.50000 13.50000", $this->coordinate->format(new DecimalDegrees()));
    }
}
