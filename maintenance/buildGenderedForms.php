<?php

namespace MediaWiki\Extension\GenderedCategories\Maintenance;
use Maintenance;
use MediaWiki\MediaWikiServices;
use MediaWiki\Sparql\SparqlClient;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

class BuildGenderedForms extends Maintenance {

	public function __construct() {
		parent::__construct();
		#$this->requireExtension( 'GenderedCategories' );
	}

	public function execute() {
		// FIXME: This table must be admin-purgeable in case of vandalism.

		$sparqlClient = new SparqlClient(
			'https://query.wikidata.org/sparql',
			MediaWikiServices::getInstance()->getHttpRequestFactory()
		);

		// TODO:
		// * Don't filter by German, store all languages.
		// * Don't store items for which there is no label or no femaleLabel in that language.
		$occupationQuery = "
			SELECT ?canonicalLabel ?femaleForm
			WHERE
			{
				?canonical wdt:P31 wd:Q28640 .
				?canonical wdt:P2521 ?femaleForm .
				FILTER (lang(?femaleForm) = \"de\")
				SERVICE wikibase:label {
					bd:serviceParam wikibase:language \"de\" .
				}
			}
		";

		$result = $sparqlClient->query( $occupationQuery );
		var_dump( $result );

		$categoryQuery = "
			SELECT ?canonicalLabel ?femaleForm
			WHERE
			{
				?canonical wdt:P31 wd:Q4167836 .
				?canonical wdt:P2521 ?femaleForm .
				FILTER (lang(?femaleForm) = \"de\")
				SERVICE wikibase:label {
					bd:serviceParam wikibase:language \"de\" .
				}
			}
		";

		$result = $sparqlClient->query( $categoryQuery );
		var_dump( $result );
	}
}

$maintClass = BuildGenderedForms::class;
require_once RUN_MAINTENANCE_IF_MAIN;
