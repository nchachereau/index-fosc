<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Index numérique de la Feuille officielle suisse du commerce (1883-2001)</title>
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
        <script>baseRoute = '{{ route('home') }}'</script>
        <script src="{{ asset('js/all.js') }}"></script>
    </head>
    <body>
        <section class="section is-medium">
            <div class="container">
                <h1 class="title is-3">Index numérique de la Feuille officielle suisse du commerce (1883-2001)</h1>
                <p class="content"><span class="is-hidden-touch">Ce qu'il manquait à la version numérisée de la
                    <a target="_blank" href="https://www.e-periodica.ch/digbib/volumes?UID=sha-001"><i>Feuille
                        officielle suisse du commerce</i> (FOSC)
                        <i class="fas fa-external-link-alt"></i></a>:
                    un moyen de passer rapidement d'une page scannée à la référence bibliographique - et
                    inversément. </span><a class="is-hidden-desktop" href="#">Plus d’informations <i class="fas fa-arrow-down"></i></a></p>
            </div>
        </section>
        @if (session('error'))
            <div class="container">
                <div class="message is-warning">
                    <div class="message-body">
                        {!! session('error') !!}
                    </div>
                </div>
            </div>
        @endif
        @if (!empty($reference))
            <div class="section">
                <div class="container">
                    <div class="notification is-primary content" id="reference-box">
                        <p>
                            <span>Référence de la page indiquée :</span>
                            <textarea class="reference" readonly wrap="soft" rows=1 style="width: {{ 47 + intval(mb_strlen($reference) - 63) }}ch">{{ $reference }}.</textarea>
                        </p>
                        <div class="buttons">
                            @include('external-link', ['text' => 'Vérifier à l’adresse indiquée'])
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if (!empty($pages))
            <div class="section">
                <div class="container">
                    <div class="notification is-primary content" id="reference-box">
                        @if (count($pages) == 1 && empty($messages))
                            <p>
                                La version numérisée de la référence indiquée se trouve à l’adresse :<br/>
                                @include('external-link', ['url' => $pages[0]['url'], 'text' => $pages[0]['url'] ]).
                            </p>
                            <p>Il s’agit de la <textarea class="reference" readonly wrap="soft" rows=1 style="width: {{ 47 + intval(mb_strlen($pages[0]['reference'])) }}ch">{{ $pages[0]['reference'] }}.</textarea> </p>
                        @else
                            <p>Votre requête était ambiguë.
                            @if (count($pages) == 1)
                                La page suivante a été trouvée : @include('external-link', ['url' => $pages[0]['url'], 'text' => $pages[0]['reference'] ])</p>
                            @else
                                Les références suivantes ont été trouvées :</p>
                                <ul>
                                    @foreach ($pages as $page)
                                        <li>{!! $page['input'] !!} : @include('external-link', ['url' => $page['url'], 'text' => $page['reference']])</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if (!empty($messages))
                                <p>Dans la collection de {{ $year }}, nous n’avons en revanche pu trouver {{ implode(' et ', $messages) }}.</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endif
        <section class="section">
            <div class="container">
                <h2 class="title is-4">Obtenir la référence d’une page</h2>
                <form method="get" action="{{ route('get_suffix') }}">
                    <div class="columns">
                        <div class="field column">
                            <label class="label" for="url">URL de la page sur e-periodica</label>
                            <div class="control is-expanded">
                                <input class="input" name="url" id="url" type="text" placeholder="https://www.e-periodica.ch/digbib/view?pid=sha-001:1887:5#86">
                            </div>
                        </div>
                    </div>
                    <div class="control">
                        <input class="button is-primary" type="submit" value="Obtenir la référence">
                    </div>
                </form>
            </div>
        </section>
        <section class="section">
            <div class="container">
                <h2 class="title is-4">Trouver une page numérisée</h2>
                <form action="{{ route('get_page') }}">
                    <div class="columns">
                        <div class="field column">
                            <label class="label" for="dt" title="Veuillez compléter ce champ.">Année <span class="comment">(ou date exacte)</span></label>
                            <div class="control">
                                <input class="input {{ $errors->has('dt') ? 'is-danger' : '' }}" name="dt" id="dt" type="text" placeholder="1887" required value="{{ old('dt') }}">
                            </div>
                            @include('field-error', ['field' => 'dt'])
                        </div>
                        <div class="field column is-narrow">
                            <label class="label" for="part">Partie</label>
                            <div class="select {{ $errors->has('part') ? 'is-danger' : '' }}">
                                <select name="part" id="part">
                                    <option></option>
                                    <option value="1" @if (old('part') == 1) selected @endif;>Iʳᵉ</option>
                                    <option value="2" @if (old('part') == 2) selected @endif;>IIᵉ</option>
                                </select>
                                @include('field-error', ['field' => 'part'])
                                <p class="help comment">Optionnel</p>
                            </div>
                        </div>
                        <div class="field column">
                            <label class="label" for="n">Cahier <span class="comment">(optionnel)</span></label>
                            <div class="control">
                                <input class="input {{ $errors->has('n') ? 'is-danger' : '' }}" name="n" id="n" type="text" placeholder="10" value=>
                                @include('field-error', ['field' => 'n'])
                            </div>
                        </div>
                        <div class="field column">
                            <label class="label" for="p" title="Veuillez compléter ce champ.">Page</label>
                            <div class="control">
                                <input class="input {{ $errors->has('p') ? 'is-danger' : '' }}" name="p" id="p" type="text" placeholder="73">
                            </div>
                            @include('field-error', ['field' => 'p'])
                        </div>
                    </div>
                    <div class="">
                        <div class="control">
                            <input class="button is-primary" type="submit" value="Trouver la page">
                        </div>
                    </div>
                </form>
            </div>
        </section>
        <footer class="footer">
            <div class="container">
                <div class="content">
                    <p>Une création de <a href="#">Nicolas Chachereau</a>.</p>
                    <p>Données extraites des interfaces d’<a href="https://www.e-periodica.ch/digbib/volumes?UID=sha-001">e-periodica</a> (ETH Zürich) et d’<a href="https://www.e-helvetica.nb.admin.ch/directAccess?callnumber=nbdig-65878">e-Helvetica</a> (Bibliothèque nationale suisse).</p>
                    <p>Technique : Site construit avec <a href="https://laravel.com/">Laravel</a>. Style basé sur <a href="https://bulma.io/">Bulma</a>.
                </div>
            </div>
        </footer>
    </body>
</html>
