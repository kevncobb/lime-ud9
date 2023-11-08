<?php

namespace Drupal\Core\Render\MainContent;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default main content renderer for modal dialog requests.
 */
class ModalRenderer extends DialogRenderer {

  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
    $response = new AjaxResponse();

    // First render the main content, because it might provide a title.
    $content = $this->renderer->renderRoot($main_content);

    // Attach the library necessary for using the OpenModalDialogCommand and set
    // the attachments for this Ajax response.
    $main_content['#attached']['library'][] = 'core/drupal.dialog.ajax';
    $response->setAttachments($main_content['#attached']);

    // If the main content doesn't provide a title, use the title resolver.
    $title = $main_content['#title'] ?? $this->titleResolver->getTitle($request, $route_match->getRouteObject());

    // Determine the dialog options for the OpenDialogCommand.
    $options = $this->getDialogOptions($request);

    $modal_selector = $main_content['#modal_selector'] ?? NULL;
    $response->addCommand(new OpenModalDialogCommand($title, $content, $options, NULL, $modal_selector));
    return $response;
  }

}
