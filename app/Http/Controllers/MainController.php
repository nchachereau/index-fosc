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
        if (!preg_match(
            '/https?:\/\/www\.e-periodica\.ch\/digbib\/view\?pid=' .
            'sha-00([12]):(\d{4}):\d+::(\d+)(?:#(\d+))?$/',
            $url,
            $matches
        )
            ) {
            return redirect('/')->with('error', "L’adresse $url ne correspond à aucune page connue.");
        }

        $suffix = (isset($matches[4])) ? $matches[4] : $matches[3];
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
            if ($record['suffix'] == $suffix) {
                $result = $record;
                break;
            }
        }
        if (empty($result)) {
            $url = session()->pull('url');
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
            $args['date'] = $args['date']->format('d.m.Y');
        }

        $csv = Reader::createFromPath(resource_path("data/$year.csv"))
            ->setHeaderOffset(0);

        foreach ($csv as $record) {
            $correctDate = (!isset($args['date']) || $args['date'] == $record['date']);
            $correctIssue = (!isset($args['issue']) || $record['issue'] == $request->input('n', ''));
            $correctPage = ($record['page'] == $request->input('p', -1));

            if ($correctPage && $correctDate && $correctIssue) {
                if (isset($args['issue']) || isset($args['date'])) {
                    $result = $record;
                    $alternativeResults = [];
                    break;
                }
            }

            if (isset($args['page']) && $correctPage) {
                $alternativeResults['page'][] = $record;
            } elseif (isset($args['issue']) && $correctIssue) {
                if (!isset($alternativeResults['issue'])) {
                    $alternativeResults['issue'][] = $record;
                }
            } elseif (isset($args['date']) && $correctDate) {
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
            $issue = ['I<sup>re</sup>', 'II<sup>e</sup>'][intval($issue[1]) - 1] . ' partie, n<sup>o</sup> ' . $issue[0];
        } else {
            $issue = 'n<sup>o</sup> ' . $issue;
        }
        $page = $record['page'];

        return "<i>Feuille officielle suisse du commerce</i>, vol. $volume, $issue, $date, p. $page";
    }

    private function createLink($year, $suffix)
    {
        $volume = intval($year) - 1882;
        return "https://www.e-periodica.ch/digbib/view?pid=sha-001:$year:$volume::$suffix#$suffix";
    }
}
