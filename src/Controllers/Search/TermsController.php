<?php

namespace AdemtiApps\Supersonic\Controllers\Search;

use AdemtiApps\Supersonic\DTOs\Action;
use AdemtiApps\Supersonic\DTOs\AddonInfo;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Statamic\Contracts\Taxonomies\TermRepository;
use Statamic\Contracts\Search\Result;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Statamic\Taxonomies\Taxonomy;
use Statamic\Facades\Search;
use Statamic\Facades\URL;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use function abort;
use function response;
use function str_starts_with;
use function strlen;

class TermsController extends CpController
{
    /**
     * @var TermRepository
     */
    protected TermRepository $termRepository;
    protected AddonInfo      $addon;

    /**
     * @param  Request  $request
     * @param  TermRepository  $termRepository
     */
    public function __construct(Request $request, TermRepository $termRepository, AddonInfo $addon)
    {
        parent::__construct($request);
        $this->termRepository = $termRepository;
        $this->addon          = $addon;
    }

    /**
     * @param  Taxonomy  $taxonomy
     * @param  Request  $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function __invoke(Taxonomy $taxonomy, Request $request)
    {
        // Only available with a Pro license.
        if ( ! $this->addon->isPro()) {
            abort(403);
        }

        if ( ! User::current()->can('edit ' . $taxonomy->handle() . ' terms', $taxonomy)) {
            abort(403);
        }

        $search = $request->input('s');
        if (empty($search)) {
            return response()->json([]);
        }

        // Check that the required index has been configured.
        $exists = Search::indexes();
        if (!$exists->has('supersonic_terms')) {
            abort(404, '', ['X-Supersonic-Error' => 'Index supersonic_terms has not been configured. Please check the install documentation.']);
        }

        try {
            $query = Search::index('supersonic_terms')
                           ->ensureExists()
                           ->search($search)
                           ->where('taxonomy', $taxonomy->handle())
                           ->where('site', Site::selected()->handle());
        } catch (\Throwable $t) {
            return response()->json([]);
        }

        return response()
            ->json(
                $query->get()
                      ->map(function (Result $result) use ($taxonomy) {
                          $id = $result->getReference();
                          if (str_starts_with($id, 'term::')) {
                              $id = substr($id, 6);
                          }
                          $siteSuffix = '::' . Site::selected()->handle();
                          if (str_ends_with($id, $siteSuffix)) {
                              $id = substr($id, 0, strlen($id) - strlen($siteSuffix));
                          }
                          $entry = $this->termRepository->find($id);

                          return new Action(
                              $result->getReference(),
                              $result->getCpTitle(),
                              '',
                              [
                                  'primary'   => [
                                      'type' => 'link',
                                      'url'  => $result->getCpUrl(),
                                  ],
                                  'secondary' => [
                                      'type' => 'link',
                                      'url'  => Url::makeAbsolute($entry->uri()),
                                  ]
                              ],
                              0,
                              Statamic::svg('icons/light/tags')
                          );
                      })
                      ->values()
            );
    }
}
