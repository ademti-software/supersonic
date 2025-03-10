<?php

namespace AdemtiApps\Supersonic\Controllers;

use AdemtiApps\Supersonic\DTOs\Action;
use AdemtiApps\Supersonic\DTOs\AddonInfo;
use Exception;
use Illuminate\Http\JsonResponse;
use Statamic\CP\Navigation\CoreNav;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection as CollectionRepository;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav as NavigationRepository;
use Statamic\Facades\Site;
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
        $actions = $this->addFieldsetActions($actions);
        $actions = $this->addBlueprintActions($actions);
        $actions = $this->addMultisiteActions($actions);

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
                    'type' => 'link',
                    'url'  => 'https://supersonic.ademti-software.co.uk',
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
                    'type' => 'link',
                    'url'  => 'https://supersonic.ademti-software.co.uk/using-supersonic',
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
                    'type' => 'link',
                    'url'  => 'https://supersonic.ademti-software.co.uk/feature-requests',
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
                    'type' => 'link',
                    'url'  => 'https://supersonic.ademti-software.co.uk/contact',
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
            $subActions = $this->enrichCpNavItemSubactions(
                $id,
                [
                    'primary' => [
                        'type' => 'link',
                        'url'  => $navItem->url(),
                    ],
                ]
            );
            $actions[$id] = new Action(
                $id,
                __($name),
                __($path),
                $subActions,
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
     * Adds some additional sub-actions to CP Nav Item actions.
     *
     * @param $id
     * @param $subActions
     *
     * @return mixed
     * @throws Exception
     */
    private function enrichCpNavItemSubactions($id, $subActions) {
        if (!$this->addon->isPro()) {
            return $subActions;
        }
        if ($id === 'content::collections') {
            $subActions['tertiary'] = [
                'type'        => 'search',
                'url'         => route( 'statamic.cp.ademti-apps.supersonic.search.entries' ),
                'permissions' => [ 'edit entries', 'configure collections' ]
            ];
        }
        return $subActions;
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
        } elseif ($repository === \Statamic\Sites\Site::class) {
            $query = $respository::all()->sortBy('name');
        } else {
            $query = $repository::all()->sortBy('title');
        }

        $query->each(function ($item) use ($parentAction, $path, &$childActions, $subActions) {
            foreach ($subActions as $idx => $subAction) {
                $hasPermission = false;
                foreach ($subAction['permissions'] as $permission) {
                    $permission    = str_replace('{handle}', $item->handle(), $permission);
                    $hasPermission |= User::current()->can($permission, $item);
                }

                // Bail if either the user doesn't have permission, or the item is not available on this site.
                if ( ! $hasPermission) {
                    unset($subActions[$idx]);
                    continue;
                }
                unset($subActions[$idx]['permissions']);
                // Generate URLs from any actions that are specified by route.
                if ( ! empty($subAction['route'])) {
                    $subActions[$idx]['url'] = route($subAction['route'], [$item->handle()]);
                    unset($subActions[$idx]['route']);
                }
                // Replace placholders in any URLs.
                if ( ! empty($subAction['url'])) {
                    $subActions[$idx]['url'] = str_replace('{handle}', $item->handle(), $subActions[$idx]['url']);
                }
            }
            if ( ! empty($subActions)) {
                if ($item instanceof \Statamic\Sites\Site) {
                    // Site has a name, not a title.
                    $title = $item->name();
                } elseif ($item instanceof \Statamic\Fields\Fieldset) {
                    // Fieldset class has a title method, but no title prop.
                    $title = $item->title();
                } else {
                    $title = $item->title;
                }

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
                    'secondary' => [
                        'type'        => 'link',
                        'route'       => 'statamic.cp.collections.edit',
                        'permissions' => ['configure collections'],
                    ],
                    'tertiary'  => [
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
                'primary'   => [
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
                    'secondary' => [
                        'type'        => 'link',
                        'route'       => 'statamic.cp.taxonomies.edit',
                        'permissions' => ['configure taxonomies',]
                    ],
                    'tertiary'  => [
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
    private function addFieldsetActions(array $actions): array
    {
        $fieldsetActions = [
            'primary' => [
                'type'        => 'link',
                'route'       => 'statamic.cp.fieldsets.edit',
                'permissions' => ['configure fields'],
            ],
        ];

        return $this->addChildActions(
            $actions,
            'fields::fieldsets',
            Fieldset::class,
            $fieldsetActions
        );
    }

    private function addMultisiteActions(array $actions): array
    {
        if ( ! Site::multiEnabled()) {
            return $actions;
        }

        return $this->addChildActions(
            $actions,
            'settings::sites',
            Site::class,
            [
                'primary' => [
                    'type'        => 'link',
                    'url'         => '/cp/select-site/{handle}',
                    'permissions' => ['access {handle} site'],
                ],
            ]
        );
    }

    private function addBlueprintActions(array $actions): array
    {
        // Collection blueprints.
        foreach (CollectionRepository::all() as $collection) {
            $actions = $this->addBlueprintChildActions(
                $actions,
                'collections',
                __('Collections'),
                $collection,
                $collection->entryBlueprints(),
                [
                    'primary' => [
                        'type'        => 'link',
                        'route'       => 'statamic.cp.collections.blueprints.edit',
                        'permissions' => ['configure fields'],
                    ],
                ]
            );
        }

        // Taxonomy blueprints.
        foreach (TaxonomyRepository::all() as $taxonomy) {
            $actions = $this->addBlueprintChildActions(
                $actions,
                'taxonomies',
                __('Taxonomies'),
                $taxonomy,
                $taxonomy->termBlueprints(),
                [
                    'primary' => [
                        'type'        => 'link',
                        'route'       => 'statamic.cp.taxonomies.blueprints.edit',
                        'permissions' => ['configure fields'],
                    ],
                ]
            );
        }

        // Navigation blueprints.
        $actions = $this->addBlueprintChildActions(
            $actions,
            'navigations',
            __('Navigations'),
            null,
            NavigationRepository::all(),
            [
                'primary' => [
                    'type'        => 'link',
                    'route'       => 'statamic.cp.navigation.blueprint.edit',
                    'permissions' => ['configure fields'],
                ],
            ]
        );

        // Globalset blueprints.
        $actions = $this->addBlueprintChildActions(
            $actions,
            'globals',
            __('Globals'),
            null,
            GlobalSet::all(),
            [
                'primary' => [
                    'type'        => 'link',
                    'route'       => 'statamic.cp.globals.blueprint.edit',
                    'permissions' => ['configure fields'],
                ],
            ]
        );

        // Asset container blueprints.
        $actions = $this->addBlueprintChildActions(
            $actions,
            'asset-containers',
            __('Asset Containers'),
            null,
            AssetContainer::all(),
            [
                'primary' => [
                    'type'        => 'link',
                    'route'       => 'statamic.cp.asset-containers.blueprint.edit',
                    'permissions' => ['configure fields'],
                ],
            ]
        );

        // Form blueprints.
        $actions = $this->addBlueprintChildActions(
            $actions,
            'forms',
            __('Forms'),
            null,
            Form::all(),
            [
                'primary' => [
                    'type'        => 'link',
                    'route'       => 'statamic.cp.forms.blueprint.edit',
                    'permissions' => ['configure form fields'],
                ],
            ]
        );

        // User blueprint.
        if (User::current()->can(['configure fields'])) {
            $actions['fields::blueprints::user'] = new Action(
                'fields::blueprints::user',
                __('User'),
                'Fields » Blueprints',
                [
                    'primary' => [
                        'type'        => 'link',
                        'url'       => route('statamic.cp.users.blueprint.edit'),
                    ],
                ],
                1
            );
            $actions['fields::blueprints::user-groups'] = new Action(
                'fields::blueprints::user-group',
                __('Group'),
                'Fields » Blueprints',
                [
                    'primary' => [
                        'type'      => 'link',
                        'url'       => route('statamic.cp.user-groups.blueprint.edit'),
                    ],
                ],
                1
            );
        }

        return $actions;
    }

    /**
     * @param  array  $actions  The current list of actions to be added to.
     * @param  string  $parentId  A conceptual "ID" to site these blueprints as a child of.
     * @param  string  $parentName  A conceptual path name to site these blueprints under.
     * @param  Entry|null  $entry  The entry that tis blueprint relates to.
     * @param  Collection  $items  The items to generate actions for.
     * @param  array  $subActions  Array of sub-actions that can be invoked on this action.
     *
     * @return array
     */
    private function addBlueprintChildActions($actions, $parentId, $parentName, $entry, $items, $subActions)
    {
        $grandparentAction = $actions['fields::blueprints'];
        $path              = $grandparentAction->path . ' » ' .
                             $grandparentAction->name . ' » ' .
                             $parentName;
        if ($entry) {
            $path .= ' » ' . $entry->title;
        }
        $childActions      = [];

        $items->each(function ($item) use ($grandparentAction, $entry, $parentId, $path, &$childActions, $subActions) {
            foreach ($subActions as $idx => $subAction) {
                $hasPermission = false;
                foreach ($subAction['permissions'] as $permission) {
                    $permission    = str_replace('{handle}', $item->handle(), $permission);
                    $hasPermission |= User::current()->can($permission, $item);
                }

                // Bail if either the user doesn't have permission, or the item is not available on this site.
                if ( ! $hasPermission) {
                    unset($subActions[$idx]);
                    continue;
                }
                unset($subActions[$idx]['permissions']);
                // Generate URLs from any actions that are specified by route.
                if ( ! empty($subAction['route'])) {
                    switch ($subAction['route']) {
                        case 'statamic.cp.collections.blueprints.edit':
                            $routeArgs = ['collection' => $entry->handle(), 'blueprint'  => $item->handle()];
                            break;
                        case 'statamic.cp.taxonomies.blueprints.edit':
                            $routeArgs = ['taxonomy'  => $entry->handle(), 'blueprint' => $item->handle()];
                            break;

                        case 'statamic.cp.navigation.blueprint.edit':
                            $routeArgs = ['navigation'  => $item->handle()];
                            break;
                        case 'statamic.cp.asset-containers.blueprint.edit':
                            $routeArgs = ['asset_container' => $item->handle()];
                            break;
                        case 'statamic.cp.globals.blueprint.edit':
                            $routeArgs = ['global_set' => $item->handle()];
                            break;
                        case 'statamic.cp.forms.blueprint.edit':
                            $routeArgs = ['form' => $item->handle()];
                            break;
                    }
                    $subActions[$idx]['url'] = route($subAction['route'], $routeArgs);
                    unset($subActions[$idx]['route']);
                }
                // Replace placholders in any URLs.
                if ( ! empty($subAction['url'])) {
                    $subActions[$idx]['url'] = str_replace('{handle}', $item->handle(), $subActions[$idx]['url']);
                }
            }
            if ( ! empty($subActions)) {
                // Add the actions
                $key                = $grandparentAction->id . '::' . $parentId . '::' . $item->handle();
                $childActions[$key] = new Action(
                    $key,
                    $item->title ?? $item->title(),
                    $path,
                    $subActions,
                    1
                );
            }
        });

        return array_merge($actions, $childActions);
    }


}
