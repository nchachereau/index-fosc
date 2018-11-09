<?php

namespace App\Http\Controllers;

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
}
