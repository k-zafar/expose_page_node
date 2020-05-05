<?php

namespace Drupal\expose_page_node\Plugin\rest\resource;

use Drupal\node\Entity\Node;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 *
 * @RestResource(
 *   id = "expose_page_node",
 *   label = @Translation("Expose Page Node"),
 *   uri_paths = {
 *     "canonical" = "/page_json/{api_key}/{nid}",
 *   }
 * )
 */

class ExposePageNode extends ResourceBase {

	/**
	 * Responds to GET requests.
	 *
	 * @return \Drupal\rest\ResourceResponse
	 *   The HTTP response object.
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 *   Throws exception expected.
	 */
	public function get($api_key, $nid) {
		$site_api_key = \Drupal::configFactory()->getEditable('system.site')->get('siteapikey');
		$node = Node::load($nid);

		// Make sure you don't trust the URL to be safe!
		if ($site_api_key != $api_key || !is_numeric($nid) || !is_object($node) || $node->getType() != 'page' || !$node->isPublished()) {
			// We will just show a standard "access denied" in this case.
			return new ResourceResponse(['message' => 'Access denied.'], 403);
		}

		// Serialize the node so that we can send node as JSON response
		$serializer = \Drupal::service('serializer');
		$data = $serializer->normalize($node);

		$response = new ResourceResponse($data);

		// In order to generate fresh result every time (without clearing
		// the cache), you need to invalidate the cache.
		$response->addCacheableDependency($data);

		return $response;
	}

}