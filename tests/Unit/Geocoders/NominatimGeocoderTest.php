<?php

namespace Maps\Test;

use FileFetcher\InMemoryFileFetcher;
use Maps\Geocoders\NominatimGeocoder;

/**
 * @covers Maps\Geocoders\NominatimGeocoder
 *
 * @licence GNU GPL v2+
 * @author Peter Grassberger < petertheone@gmail.com >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class NominatimGeocoderTest extends \PHPUnit_Framework_TestCase {

	const NEW_YORK_FETCH_URL = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=New+York';

	public function testHappyPath() {
		$fileFetcher = new InMemoryFileFetcher( [
			self::NEW_YORK_FETCH_URL
				=> '[{"place_id":"97961780","licence":"Data © OpenStreetMap contributors, ODbL 1.0. http:\/\/www.openstreetmap.org\/copyright","osm_type":"way","osm_id":"161387758","boundingbox":["40.763858","40.7642664","-73.9548572","-73.954092"],"lat":"40.7642499","lon":"-73.9545249","display_name":"NewYork Hospital Drive, Upper East Side, Manhattan, New York County, New York City, New York, 10021, United States of America","place_rank":"27","category":"highway","type":"service","importance":0.275}]'
		] );

		$geocoder = new NominatimGeocoder( $fileFetcher );

		$this->assertSame( 40.7642499, $geocoder->geocode( 'New York' )->getLatitude() );
		$this->assertSame( -73.9545249, $geocoder->geocode( 'New York' )->getLongitude() );
	}

	public function testWhenFetcherThrowsException_nullIsReturned() {
		$geocoder = new NominatimGeocoder( new InMemoryFileFetcher( [] ) );

		$this->assertNull( $geocoder->geocode( 'New York' ) );
	}

	/**
	 * @dataProvider invalidResponseProvider
	 */
	public function testWhenFetcherReturnsInvalidResponse_nullIsReturned( $invalidResponse ) {
		$geocoder = new NominatimGeocoder( new InMemoryFileFetcher( [
			self::NEW_YORK_FETCH_URL => $invalidResponse
		] ) );

		$this->assertNull( $geocoder->geocode( 'New York' ) );
	}

	public function invalidResponseProvider() {
		return [
			'Not JSON' => [ '~=[,,_,,]:3' ],
			'Not a JSON array' => [ '42' ],
			'Empty JSON array' => [ '[]' ],
			'Missing lon key' => [ '[{"lat":"40.7642499","FOO":"-73.9545249"}]' ],
			'Missing lat key' => [ '[{"FOO":"40.7642499","lon":"-73.9545249"}]' ],
		];
	}

	// TODO: test malicious address escaping

}
