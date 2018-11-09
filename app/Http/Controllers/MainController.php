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
        $volume = intval($year) - 1882;

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
                'reference' => "<i>Feuille officielle suisse du commerce</i>, vol. $volume, n<sup>o</sup> {$record['issue']}, {$record['date']}, p. {$record['page']}",
                'url' => "https://www.e-periodica.ch/digbib/view?pid=sha-001:$year:$volume::$suffix",
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
        $attr = $v->validate();

        if (is_numeric($attr['dt'])) {
            $year = intval($attr['dt']);
        } else {
            $dt = \DateTime::createFromFormat('d#m#Y', $attr['dt']);
            $year = $dt->format('Y');
        }

        $csv = Reader::createFromPath(resource_path("data/$year.csv"))
            ->setHeaderOffset(0);

        foreach ($csv as $record) {
            $correctDate = (!isset($dt) || $dt->format('d.m.Y') == $record['date']);
            $correctIssue = ($record['issue'] == $request->input('n', ''));
            $correctPage = ($record['page'] == $request->input('p', -1));

            if ($correctPage && $correctDate && $correctIssue) {
                $result = $record;
                $alternativeResults = [];
                break;
            }

            if ($request->filled('p') && $correctPage) {
                $alternativeResults['page'] = $record;
            } elseif ($request->filled('n') && $correctIssue) {
                if (!isset($alternativeResults['issue'])) {
                    $alternativeResults['issue'] = $record;
                }
            } elseif (isset($dt) && $correctDate) {
                if (!isset($alternativeResults['date'])) {
                    $alternativeResults['date'] = $record;
                }
            }
        }

        $volume = $year - 1882;
        $messages = [];
        if (isset($result)) {
            $suffix = $result['suffix'];
            $urls[] = "https://www.e-periodica.ch/digbib/view?pid=sha-001:$year:$volume::$suffix";
        } else {
            if ($request->filled('p')) {
                if (!empty($alternativeResults['page'])) {
                    $suffix = $alternativeResults['page']['suffix'];
                    $urls[] = "https://www.e-periodica.ch/digbib/view?pid=sha-001:$year:$volume::$suffix";
                } else {
                    $messages[] = 'aucune page ' . $request->input('p');
                }
            }
            if (isset($dt)) {
                if (!empty($alternativeResults['date'])) {
                    $suffix = $alternativeResults['date']['suffix'];
                    $urls[] = "https://www.e-periodica.ch/digbib/view?pid=sha-001:$year:$volume::$suffix";
                } else {
                    $messages[] = 'aucun cahier daté du ' . $dt->format('j.n.Y');
                }
            }
            if ($request->filled('n')) {
                if (!empty($alternativeResults['issue'])) {
                    $suffix = $alternativeResults['issue']['suffix'];
                    $urls[] = "https://www.e-periodica.ch/digbib/view?pid=sha-001:$year:$volume::$suffix";
                } else {
                    $messages[] = 'aucun cahier numéroté ' . $request->input('n');
                }
            }
        }

        if (empty($urls)) {
            return redirect('/')->with(
                'error',
                "Aucune page trouvée dans la FOSC de $year : " . implode(', ', $messages) . '.'
            );
        }

        return view(
            'welcome',
            [
                'urls' => $urls,
                'messages' => $messages,
            ]
        );
    }
}
