<?php

namespace Drupal\fsa_custom\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Builds a block with links.
 *
 * @Block(
 *   id = "fsa_link_tabs",
 *   admin_label = @Translation("Link tabs block")
 * )
 *
 */
class LinkTabsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /* @var \Drupal\Core\Routing\RouteProviderInterface $routeProvider */
  protected $routeProvider;

  /** @var \Drupal\Core\Utility\Token $token */
  protected $token;

  /**
   * LinkTabsBlock constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, RouteProviderInterface $route_provider, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeProvider = $route_provider;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('token')
    );
  }

  /**
   * Returns link indices.
   *
   * @return array
   */
  protected function getLinkIndices() {
    $indices = [];

    foreach (range(0, 4) as $delta) {
      $indices[] = 'link-' . $delta;
    }

    return $indices;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = [
      'class' => '',
    ];

    foreach ($this->getLinkIndices() as $delta => $name) {
      $defaults['links'][$name] = [
        'url' => '',
        'text' => '',
        'route_parameters' => '',
      ];
    }

    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Item list classes'),
      '#description' => $this->t('Enter an item list class names separated by a space.'),
      '#default_value' => $this->configuration['class'],
    ];

    foreach ($this->getLinkIndices() as $delta => $name) {
      $form['links'][$name] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $this->t('Link #@delta', ['@delta' => $delta]),
        'url' => [
          '#type' => 'textfield',
          '#title' => $this->t('Route name or URL'),
          '#description' => $this->t('Enter a route name or a URL. URL should start with a slash.'),
          '#default_value' => $this->configuration['links'][$name]['url'],
        ],
        'route_parameters' => [
          '#type' => 'textfield',
          '#title' => $this->t('Route parameters'),
          '#description' => $this->t('Provide parameters in json format. Example: %example. Tokens can be used.', ['%example' => '{"value": 123, "foo": "bar"}']),
          '#default_value' => $this->configuration['links'][$name]['route_parameters'],
        ],
        'text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Link text'),
          '#description' => $this->t('Enter a text for a link.'),
          '#default_value' => $this->configuration['links'][$name]['text'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    parent::blockValidate($form, $form_state);

    foreach ($this->getLinkIndices() as $delta => $name) {
      $url_parents = ['links', $name, 'url'];

      if ($url = $form_state->getValue($url_parents)) {
        try {
          // If route exists, no exceptions will be thrown.
          $this->routeProvider->getRouteByName($url);
        } catch (RouteNotFoundException $e) {
          try {
            Url::fromUserInput($url);
          } catch (\InvalidArgumentException $e) {
            $form_state->setErrorByName(implode('][', ['settings', 'links', $name, 'url']), $e->getMessage());
            return;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['class'] = $form_state->getValue('class');
    $this->configuration['links'] = [];

    foreach ($this->getLinkIndices() as $delta => $name) {
      if ($text = $form_state->getValue(['links', $name, 'text'])) {
        if ($url = $form_state->getValue(['links', $name, 'url'])) {
          $this->configuration['links'][$name]['url'] = $url;
          $this->configuration['links'][$name]['text'] = $text;
          $this->configuration['links'][$name]['route_parameters'] = $form_state->getValue(['links', $name, 'route_parameters']);
        }
      }
    }
  }

  /**
   * Returns available context as token data.
   *
   * @return array
   */
  protected function getContextAsTokenData() {
    $data = [];
    foreach ($this->getContexts() as $context) {
      // @todo Simplify this when token and typed data types are unified in
      //   https://drupal.org/node/2163027.
      if (strpos($context->getContextDefinition()->getDataType(), 'entity:') === 0) {
        $token_type = substr($context->getContextDefinition()->getDataType(), 7);

        if ($token_type == 'taxonomy_term') {
          $token_type = 'term';
        }

        if ($context_value = $context->getContextValue()) {
          $data[$token_type] = $context_value;
        }
      }
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if (!empty($this->configuration['links'])) {
      $build = [
        '#theme' => 'item_list',
        '#items' => [],
        '#attributes' => [
          'class' => explode(' ', $this->configuration['class']),
        ],
      ];

      // Get token data from contexts.
      $data = $this->getContextAsTokenData();

      foreach ($this->configuration['links'] as $link) {
        if (!empty($link['url'])) {
          // Prepare URL.
          $url = NULL;
          // Get route parameters.
          $route_parameters = Json::decode($this->token->replace($link['route_parameters'], $data));

          try {
            // If route exists, no exceptions will be thrown.
            $route = $this->routeProvider->getRouteByName($link['url']);
            $url = Url::fromRoute($route, is_array($route_parameters) ? $route_parameters : []);
            $url->toString();
          } catch (RouteNotFoundException $e) {
            try {
              $url = Url::fromUserInput($link['url']);
            } catch (\Exception $e) {
              watchdog_exception('csb_base', $e);
            }
          } catch (\Exception $e) {
            watchdog_exception('csb_base', $e);
          }

          if ($url) {
            $item_link = Link::fromTextAndUrl($link['text'], $url);
            $item_link->getUrl()->setOption('set_active_class', TRUE);

            $build['#items'][] = $item_link->toString();
          }
        }
      }
    }

    return $build;
  }

}
