<?php

namespace AdemtiApps\Supersonic\Controllers\Search;

use AdemtiApps\Supersonic\DTOs\Action;
use AdemtiApps\Supersonic\DTOs\AddonInfo;
use Illuminate\Http\Request;
use Statamic\Contracts\Entries\EntryRepository;
use Statamic\Contracts\Search\Result;
use Statamic\Entries\Collection;
use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;
use function response;
use function str_starts_with;

class CollectionEntriesController extends CpController
{
    /**
     * @var EntryRepository
     */
    protected EntryRepository $entryRepository;
    protected AddonInfo       $addon;

    /**
     * @param  Request  $request
     * @param  EntryRepository  $entryRepository
     */
    public function __construct(Request $request, EntryRepository $entryRepository, AddonInfo $addon)
    {
        parent::__construct($request);
        $this->entryRepository = $entryRepository;
        $this->addon           = $addon;
    }

    /**
     * @param  Collection  $collection
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(Collection $collection, Request $request)
    {
        // Only available with a Pro license.
        if ( ! $this->addon->isPro()) {
            abort(403);
        }

        if ( ! User::current()->can('edit ' . $collection . ' entries', $collection) &&
             ! User::current()->can('configure collections')
        ) {
            abort(403);
        }

        $search = $request->input('s');
        if (empty($search)) {
            return response()->json([]);
        }


        try {
            $query = Search::index('supersonic_entries')
                           ->ensureExists()
                           ->search($search)
                           ->where('collection', $collection->handle())
                           ->where('site', Site::selected()->handle());
        } catch (\Throwable $t) {
            return response()->json([]);
        }

        return response()
            ->json(
                $query->get()
                      ->map(function (Result $result) {
                          $id = $result->getReference();
                          if (str_starts_with($id, 'entry::')) {
                              $id = substr($id, 7);
                          }

                          $entry        = $this->entryRepository->find($id);
                          $entryActions = [
                              'primary' => [
                                  'type' => 'link',
                                  'url'  => $result->getCpUrl(),
                              ],
                          ];
                          if ( ! empty($entry->uri())) {
                              $entryActions['secondary'] = [
                                  'type' => 'link',
                                  'url'  => Url::makeAbsolute($entry->uri()),
                              ];
                          }

                          return new Action(
                              $result->getReference(),
                              $result->getCpTitle(),
                              '',
                              $entryActions,
                              0,
                              Statamic::svg('icons/light/content-writing')
                          );
                      })
                      ->values()
            );
    }
}