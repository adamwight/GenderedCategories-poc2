<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\GenderedCategories;

use HtmlArmor;
use MediaWiki\MediaWikiServices;
use OutputPage;

class Hooks {

	// TODO: Building this map is a whole thing we're not getting into yet.
	static private $demoMap = [
		'Arzt' => 'Ärztin',
		'Actor' => 'Actress',
	];
	static private $requiredCategory = 'Frau';


	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageMakeCategoryLinks
	 * @param OutputPage &$out
	 * @param array $categories associative array, keys are category names, values are category types ("normal" or "hidden")
	 * @param array &$links intended to hold the result. Must be an associative array with category types as keys and arrays of HTML links as values.
	 */
	public static function onOutputPageMakeCategoryLinks( OutputPage &$out, array $categories, array &$links ) {
		global $wgGenderedCategoriesEnableRewrite;

		if ( false === $wgGenderedCategoriesEnableRewrite
			|| !array_key_exists( self::$requiredCategory, $categories )
		) {
			return;
		}

		$services = MediaWikiServices::getInstance();
		$linkRenderer = $services->getLinkRenderer();
		$contentLanguage = $services->getContentLanguage();
		$links = [];
		foreach ( $categories as $category => $type ) {
			$category = (string)$category;
			$origcategory = $category;
			$title = \Title::makeTitleSafe( NS_CATEGORY, $category );
			if ( !$title ) {
				continue;
			}
			$contentLanguage->findVariantLink( $category, $title, true );
			if ( $category != $origcategory && array_key_exists( $category, $categories ) ) {
				continue;
			}
			$rewrittenCategory = self::rewriteCategory( $category );
			$text = $contentLanguage->convertHtml( $rewrittenCategory );
			$links[$type][] = $linkRenderer->makeLink( $title, new HtmlArmor( $text ) );
		}

		// Prevent default processing.
		return false;
	}

	private static function rewriteCategory( $category ) {
		// TODO: Lots of nuances to address here: other genders, regex construction per language...
		foreach ( self::$demoMap as $canonical => $gendered ) {
			$category = str_replace( $canonical, $gendered, $category );
		}
		return $category;
	}

}
