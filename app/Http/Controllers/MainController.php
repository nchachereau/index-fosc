<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use League\Csv\Reader;

class MainController extends Controller
{
    public function home()
    {
        return view('welcome');
    }

    public function getSuffix(Request $request)
    {
        if (!$request->filled('url')) {
            return redirect('/');
        }
        $url = $request->input('url');
        session(['url' => $url]);

        $example = 'par exemple <a href="' .
            route('get_suffix', ['url' => 'https://www.e-periodica.ch/digbib/view?pid=sha-001:1896:14::412#412']) .
            '">https://www.e-periodica.ch/digbib/view?pid=sha-001:1896:14::412#412</a> ?';

        if (!preg_match(
            '/^https?:\/\//',
            $url
        )) {
            return redirect('/')->with(
                'error',
                e($url) . ' ne ressemble pas à une URL. ' .
                'Et si vous essayiez avec une numérisation de la FOSC ' .
                'sur le site e-periodica, ' . $example
            );
        }

        if (!preg_match(
            '/^https?:\/\/www\.e-periodica\.ch/',
            $url
        )) {
            return redirect('/')->with(
                'error',
                'L’adresse ' . e($url) . ' n’est pas prise en charge. ' .
                'Et si vous essayiez avec une numérisation de la FOSC ' .
                'sur le site e-periodica, ' . $example
            );
        }

        if (preg_match(
            '/^https?:\/\/www\.e-periodica\.ch\/digbib\/(doasearch|hitlist)/',
            $url
        )) {
            return redirect('/')->with(
                'error',
                'Malheureusement, la page des résultats de recherche ' .
                'n’est pas prise en charge. En effet, l’URL n’indique pas ' .
                'quel numéro de la FOSC est affiché. ' .
                'Ouvrez la page qui vous intéresse et indiquez-en l’URL ci-dessous.'
            );
        }

        if (preg_match(
            '/^https?:\/\/www\.e-periodica\.ch\/digbib\/view\?pid=sha-002/',
            $url
        )) {
            return redirect('/')->with(
                'error',
                'Désolé, les pages de <i>fosc.ch</i> (à partir de 2002) ne sont pas encore prises en charge.'
            );
        }

        if (!preg_match(
            '/^https?:\/\/www\.e-periodica\.ch\/digbib\/view\?pid=' .
            'sha-00([12]):(\d{4}):\d+(?:::(\d+))?(?:#(\d+))?$/',
            $url,
            $matches
        )
            ) {
            $url = e($url);
            return redirect('/')->with('error', "L’adresse $url ne correspond à aucune page connue.");
        }

        if (isset($matches[3])) {
            $suffix = (isset($matches[4])) ? $matches[4] : $matches[3];
        } else {
            $suffix = 1;
        }
        return redirect()->route(
            'get_reference',
            [
                'year' => $matches[2],
                'suffix' => $suffix,
            ]
        );
    }

    public function reference($year, $suffix)
    {
        if (!is_numeric($year) || !is_numeric($suffix)) {
            return redirect('/');
        } elseif (intval($year) < 1881 || intval($year) > 2001) {
            return redirect('/');
        }

        $csv = Reader::createFromPath(resource_path("data/$year.csv"))
            ->setHeaderOffset(0);

        foreach ($csv as $record) {
            if ($suffix == '1') {
                // special case, not a real suffix, just return first line
                $result = $record;
                $suffix = $record['suffix'];
            }
            if ($record['suffix'] == $suffix) {
                $result = $record;
                break;
            }
        }
        if (empty($result)) {
            $url = e(session()->pull('url'));
            return redirect('/')->with('error', "L’adresse $url ne correspond à aucune page connue.");
        }

        return view(
            'welcome',
            [
                'reference' => $this->formatReference($record),
                'url' => $this->createLink($year, $suffix),
            ]
        );
    }

    public function page(Request $request)
    {
        $v = Validator::make($request->all(), [
            'dt' => 'required|regex:%^(\d{1,2}[-\\./]\d{1,2}[-\\./])?\d{4}$%',
        ]);
        $v->sometimes('dt', 'integer|between:1883,2001', function ($input) {
            return is_numeric($input->dt);
        });
        $v->sometimes('dt', 'date|before:1.1.2002', function ($input) {
            return !is_numeric($input->dt);
        });
        $v->sometimes('p', 'required', function ($input) {
            return is_numeric($input->dt);
        });
        $v->validate();

        $args = [];
        if ($request->filled('p')) {
            $args['page'] = $request->input('p');
        }
        if ($request->filled('n')) {
            $args['issue'] = $request->input('n');
        }

        if (is_numeric($request->input('dt'))) {
            $year = intval($request->input('dt'));
        } else {
            $args['date'] = \DateTime::createFromFormat('d#m#Y', $request->input('dt'));
            $year = $args['date']->format('Y');
        }

        if ($year == 1899 && $request->input('p', -1) == 1073) {
            return redirect('/')->with(
                'error',
                '<p>La page 1073 de l’année 1899 de la FOSC aurait dû se trouver à l’adresse: <br>' .
                'https://www.e-periodica.ch/digbib/view?pid=sha-001:1899:17::1342#1342</p>' .
                '<p>Malheureusement, c’est la page 67 qui a été numérisée à sa place. ' .
                'Le problème va être signalé à la plateforme e-periodica.</p>'
            );
        }

        $csv = Reader::createFromPath(resource_path("data/$year.csv"))
            ->setHeaderOffset(0);

        foreach ($csv as $record) {
            $correct = [
                'date' => (!isset($args['date']) || $args['date']->format('d.m.Y') == $record['date']),
                'issue' => (!isset($args['issue']) || $record['issue'] == $args['issue']),
                'page' => ($record['page'] == $request->input('p', -1)),
            ];

            if (count($args) > 1) {
                // filter values asked for
                $matches = array_intersect_key($correct, $args);
                if (count(array_filter($matches)) == count($args)) {
                    // perfect match
                    $result = $record;
                    $alternativeResults = [];
                    break;
                }
            }

            if (isset($args['page']) && $correct['page']) {
                // do not report second result for exact same page
                if (!empty($alternativeResults['page'])) {
                    if ($alternativeResults['page'][0]['page'] == $record['page'] &&
                        $alternativeResults['page'][0]['issue'] == $record['issue'] &&
                        $alternativeResults['page'][0]['date'] == $record['date']) {
                        continue;
                    }
                }
                $alternativeResults['page'][] = $record;
            }
            if (isset($args['issue']) && $correct['issue']) {
                if (!isset($alternativeResults['issue'])) {
                    $alternativeResults['issue'][] = $record;
                }
            } elseif (isset($args['issue']) && $year == 1883) {
                if ($args['issue'] == explode('.', $record['issue'])[0]) {
                    if (!isset($alternativeResults['issue'])) {
                        $alternativeResults['issue'][] = $record;
                    } else {
                        $knownIssues = array_reduce($alternativeResults['issue'], function ($c, $i) {
                            $c[] = $i['issue'];
                            return $c;
                        }, []);
                        if (!in_array($record['issue'], $knownIssues)) {
                            $alternativeResults['issue'][] = $record;
                        }
                    }
                }
            }
            if (isset($args['date']) && $correct['date']) {
                if (!isset($alternativeResults['date'])) {
                    $alternativeResults['date'][] = $record;
                }
            }
        }

        $volume = $year - 1882;
        $messages = [];
        if (isset($result)) {
            $suffix = $result['suffix'];
            $pages[] = [
                'url' => $this->createLink($year, $suffix),
                'reference' => $this->formatReference($result),
            ];
        } else {
            $messageCollection = [
                'page' => 'aucune page',
                'date' => 'aucun cahier daté du',
                'issue' => 'aucun cahier numéroté',
            ];
            $inputCollection = [
                'page' => 'p. ',
                'date' => '',
                'issue' => 'n<sup>o</sup> ',
            ];
            if (isset($args['date'])) {
                $args['date'] = $args['date']->format('j.n.Y');
            }
            foreach ($args as $arg => $value) {
                if (!empty($alternativeResults[$arg])) {
                    foreach ($alternativeResults[$arg] as $res) {
                        $suffix = $res['suffix'];
                        $pages[] = [
                            'url' => $this->createLink($year, $suffix),
                            'reference' => $this->formatReference($res),
                            'input' => $inputCollection[$arg] . e($value),
                        ];
                    }
                } else {
                    $messages[] = $messageCollection[$arg] . ' ' . $value;
                }
            }
        }

        if (empty($pages)) {
            return redirect('/')->with(
                'error',
                "Aucune page trouvée dans la FOSC de $year : " . implode(', ', $messages) . '.'
            );
        }

        return view(
            'welcome',
            [
                'pages' => $pages,
                'messages' => $messages,
                'year' => $year,
            ]
        );
    }

    private function formatReference($record)
    {
        $date = \DateTime::createFromFormat('d#m#Y', $record['date']);
        $volume = intval($date->format('Y')) - 1882;
        $date = $date->format('j.n.Y');
        $issue = $record['issue'];
        if ($volume == 1) {
            $issue = explode('.', $issue);
            $issue = ['Iʳᵉ', 'IIᵉ'][intval($issue[1]) - 1] . ' partie, nᵒ ' . $issue[0];
        } else {
            $issue = 'nᵒ ' . $issue;
        }
        $page = $record['page'];

        return "Feuille officielle suisse du commerce, vol. $volume, $issue, $date, p. $page";
    }

    private function createLink($year, $suffix)
    {
        $volume = intval($year) - 1882;
        return "https://www.e-periodica.ch/digbib/view?pid=sha-001:$year:$volume::$suffix#$suffix";
    }
}
