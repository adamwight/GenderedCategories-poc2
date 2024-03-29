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

use Content;
use LinksUpdate;
use Revision;
use Status;
use Title;
use User;
use WikiPage;

class Hooks {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/LinksUpdate
	 * @param LinksUpdate $linksUpdate
	 */
	public static function onLinksUpdate( LinksUpdate &$linksUpdate ) {
		$catsToAdd = [];
		foreach ( $linksUpdate->mCategories as $category => $sortKey ) {
			$title = Title::makeTitleSafe( NS_CATEGORY, $category );
			$page = new WikiPage( $title );
			$target = $page->getRedirectTarget();
			if ( $target !== null ) {
				$catsToAdd[$target->getDBkey()] = $sortKey;
			}
		}

		$linksUpdate->mCategories = array_merge( $linksUpdate->mCategories, $catsToAdd );
	}

	/**
	 * When a category is saved we should call LinksUpdate on all members in case there is a
	 * new redirect or a redirect was removed.
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete
	 */
	public static function onPageContentSaveComplete(
		WikiPage $wikiPage, User $user, Content $mainContent, $summaryText, $isMinor, $isWatch,
		$section, &$flags, Revision $revision, Status $status, $originalRevId, $undidRevId
	) {
		if ( $wikiPage->getTitle()->getNamespace() === NS_CATEGORY ) {
			LinksUpdate::queueRecursiveJobsForTable(
				$wikiPage->getTitle(),
				'categorylinks',
				// FIXME: real action here.
				'category-save',
				$user->getName() );
		}
	}

}
