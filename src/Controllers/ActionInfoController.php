<?php

namespace AdemtiApps\Supersonic\Controllers;

use AdemtiApps\Supersonic\DTOs\Action;
use AdemtiApps\Supersonic\DTOs\AddonInfo;
use Exception;
use Illuminate\Http\JsonResponse;
use Statamic\CP\Navigation\CoreNav;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection as CollectionRepository;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Nav as NavigationRepository;
use Statamic\Facades\Taxonomy as TaxonomyRepository;
use Statamic\Facades\User;
use Statamic\Statamic;
use function array_merge;
use function response;
use function route;
use function str_replace;
use function uasort;

class ActionInfoController
{
    /**
     * @var AddonInfo
     */
    protected AddonInfo $addon;

    /**
     * @param  AddonInfo  $addon
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function __invoke(AddonInfo $addon)
    {
        $this->addon = $addon;

        // Generate the action list.
        $actions = $this->getCpNavActions();
        $actions = $this->addCollectionActions($actions);
        $actions = $this->addNavigationActions($actions);
        $actions = $this->addTaxonomyActions($actions);
        $actions = $this->addAssetContainerActions($actions);
        $actions = $this->addBlueprintActions($actions);

        // Sort them.
        uasort($actions, function ($a, $b) {
            return ($a->path . ' » ' . $a->name) > ($b->path . ' » ' . $b->name);
        });

        // @TODO: Add actions for switching sites

        // Add "Supersonic" links at the end.
        $actions['supersonic']           = new Action(
            'supersonic',
            'Supersonic',
            'Supersonic',
            [
                'primary' => [
                    'type'        => 'link',
                    'url'         => 'https://supersonic.ademti-software.co.uk',
                ]
            ],
            0
        );
        $actions['supersonic::docs']     = new Action(
            'supersonic::docs',
            'Documentation',
            'Supersonic',
            [
                'primary' => [
                    'type'        => 'link',
                    'url'         => 'https://supersonic.ademti-software.co.uk/using-supersonic',
                ]
            ],
            1
        );
        $actions['supersonic::feedback'] = new Action(
            'supersonic::feedback',
            'Send feedback',
            'Supersonic',
            [
                'primary' => [
                    'type'        => 'link',
                    'url'         => 'https://supersonic.ademti-software.co.uk/feature-requests',
                ]
            ],
            1
        );
        $actions['supersonic::support']  = new Action(
            'supersonic::support',
            'Get support',
            'Supersonic',
            [
                'primary' => [
                    'type'        => 'link',
                    'url'         => 'https://supersonic.ademti-software.co.uk/contact',
                ]
            ],
            1
        );

        return response()
            ->json($actions);
    }

    /**
     * @return array
     */
    private function getCpNavActions(): array
    {
        // Fetch the CP nav menu
        CoreNav::make();
        $navItems = Nav::items();

        $actions = [];
        foreach ($navItems as $navItem) {

            $authorization = $navItem->authorization();
            if ( ! empty($authorization) &&
                 ! User::current()->can($authorization->ability, $authorization->arguments)
            ) {
                continue;
            }
            $id      = $navItem->id();
            $section = $navItem->section();
            $name    = '';
            $path    = '';

            // Calculate the name.
            if ($section !== 'Top Level') {
                $path = $section;
            }
            $name         .= $navItem->display();
            $actions[$id] = new Action(
                $id,
                $name,
                $path,
                [
                    'primary' => [
                        'type' => 'link',
                        'url'  => $navItem->url(),
                    ],
                ],
                0,
                Statamic::svg('icons/light/' . ($navItem->icon() ?? 'entries'))
            );

            $path     = $path . ' » ' . $name;
            $children = $navItem->children();
            if ( ! $children) {
                continue;
            }
            foreach ($children as $child) {
                $id           = $child->id();
                $name         = $child->display();
                $actions[$id] = new Action(
                    $id,
                    $name,
                    $path,
                    [
                        'primary' => [
                            'type' => 'link',
                            'url'  => $child->url(),
                        ],
                    ],
                    1
                );
            }
        }

        return $actions;
    }

    /**
     * @param $actions
     * @param $parentId
     * @param $repository
     * @param $permission
     * @param $route
     * @param $type
     *
     * @return array
     */

    /**
     * @param  array  $actions  The current list of actions to be added to.
     * @param  string  $parentId  The parent that these actions should have their path calculated relative to
     * @param  Object  $repository  The repository called to pull a list of items
     * @param  array  $subActions  Array of sub-actions that can be invoked on this action
     *
     * @return array
     */
    private function addChildActions($actions, $parentId, $repository, $subActions)
    {
        $parentAction = $actions[$parentId];
        $path         = $parentAction->path . ' » ' . $parentAction->name;
        $childActions = [];

        // Special case for the Fieldset class which has a title method, but no title prop.
        if ($repository === Fieldset::class) {
            $query = $repository::all()->sort(fn($a, $b) => $a->title() > $b->title());
        } else {
            $query = $repository::all()->sortBy('title');
        }

        $query->each(function ($item) use ($parentAction, $path, &$childActions, $subActions) {
            // Generate URLs from any actions that are specified by route.
            foreach ($subActions as $idx => $subAction) {
                $hasPermission = false;
                foreach ($subAction['permissions'] as $permission) {
                    $permission    = str_replace('{handle}', $item->handle(), $permission);
                    $hasPermission |= User::current()->can($permission, $item);
                }

                // Bail if either the user doesn't have permission, or the item is not available on this site.
                if ( ! $hasPermission ) {
                    unset($subActions[$idx]);
                    continue;
                }
                unset($subActions[$idx]['permissions']);
                if ( ! empty($subAction['route'])) {
                    $subActions[$idx]['url'] = route($subAction['route'], [$item->handle()]);
                    unset($subActions[$idx]['route']);
                }
            }
            if ( ! empty($subActions)) {
                // Special case for the Fieldset class which has a title method, but no title prop.
                $title = $item->title ?? $item->title();
                // Add the actions
                $childActions[$parentAction->id . '::' . $item->handle()] = new Action(
                    $parentAction->id . '::' . $item->handle(),
                    $title,
                    $path,
                    $subActions,
                    1
                );
            }
        });

        return array_merge($actions, $childActions);
    }

    /**
     * @param  array  $actions
     *
     * @return array
     * @throws Exception
     */
    private function addCollectionActions(array $actions): array
    {
        $collectionActions = [
            'primary' => [
                'type'        => 'link',
                'route'       => 'statamic.cp.collections.show',
                'permissions' => ['view {handle} entries', 'configure collections']
            ]
        ];
        if ($this->addon->isPro()) {
            $collectionActions = array_merge(
                $collectionActions,
                [
                    'secondary'  => [
                        'type'        => 'link',
                        'route'       => 'statamic.cp.collections.edit',
                        'permissions' => ['configure collections'],
                    ],
                    'tertiary' => [
                        'type'        => 'search',
                        'route'       => 'statamic.cp.ademti-apps.supersonic.search.collection-entries',
                        'permissions' => ['edit {handle} entries', 'configure collections']
                    ],
                ]
            );
        }

        return $this->addChildActions(
            $actions,
            'content::collections',
            CollectionRepository::class,
            $collectionActions
        );
    }

    /**
     * @param  array  $actions
     *
     * @return array
     */
    private function addNavigationActions(array $actions): array
    {
        return $this->addChildActions(
            $actions,
            'content::navigation',
            NavigationRepository::class,
            [
                'primary' => [
                    'type'        => 'link',
                    'route'       => 'statamic.cp.navigation.show',
                    'permissions' => ['edit {handle} nav', 'configure navs']
                ],
                'secondary' => [
                    'type'        => 'link',
                    'route'       => 'statamic.cp.navigation.edit',
                    'permissions' => ['edit {handle} nav', 'configure navs']
                ],
            ]
        );
    }

    /**
     * @param  array  $actions
     *
     * @return array
     */
    private function addTaxonomyActions(array $actions): array
    {
        $taxonomyActions = [
            'primary' => [
                'type'        => 'link',
                'route'       => 'statamic.cp.taxonomies.show',
                'permissions' => ['view {handle} terms', 'configure taxonomies']
            ]
        ];
        if ($this->addon->isPro()) {
            $taxonomyActions = array_merge(
                $taxonomyActions,
                [
                    'secondary'  => [
                        'type'        => 'link',
                        'route'       => 'statamic.cp.taxonomies.edit',
                        'permissions' => ['configure taxonomies',]
                    ],
                    'tertiary' => [
                        'type'        => 'search',
                        'route'       => 'statamic.cp.ademti-apps.supersonic.search.terms',
                        'permissions' => ['edit {handle} terms', 'configure taxonomies']
                    ],
                ]
            );
        }

        return $this->addChildActions(
            $actions,
            'content::taxonomies',
            TaxonomyRepository::class,
            $taxonomyActions
        );
    }

    /**
     * @param  array  $actions
     *
     * @return array
     */
    private function addAssetContainerActions(array $actions): array
    {

        $assetContainerActions = [
            'primary' => [
                'type'        => 'link',
                'route'       => 'statamic.cp.assets.browse.show',
                'permissions' => ['index', 'configure asset containers']
            ],
        ];
        if ($this->addon->isPro()) {
            $assetContainerActions = array_merge(
                $assetContainerActions,
                [
                    // @TODO - add search action for asset container items
                    'secondary' => [
                        'type'        => 'link',
                        'route'       => 'statamic.cp.asset-containers.edit',
                        'permissions' => ['configure asset containers']
                    ],
                ]
            );
        }

        return $this->addChildActions(
            $actions,
            'content::assets',
            AssetContainer::class,
            $assetContainerActions
        );
    }

    /**
     * @param  array  $actions
     *
     * @return array
     */
    private function addBlueprintActions(array $actions): array
    {
        // @TODO - Add per-blueprint actions - behind licensing
        return $this->addChildActions(
            $actions,
            'fields::fieldsets',
            Fieldset::class,
            [
                'primary' => [
                    'type'        => 'link',
                    'route'       => 'statamic.cp.fieldsets.edit',
                    'permissions' => ['configure fields'],
                ],
            ]
        );
    }

}
