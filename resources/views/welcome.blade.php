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
                <p class="content"><span class="is-hidden-touch">Grâce à sa numérisation, la
                    <a target="_blank" href="https://www.e-periodica.ch/digbib/volumes?UID=sha-001"><i>Feuille
                        officielle suisse du commerce</i> (FOSC)
                        <i class="fas fa-external-link-alt"></i></a>
                    est appelée à devenir une ressource importante pour étudier l’histoire
                    économique et sociale de la Suisse contemporaine. À l’usage, il manque
                    pourtant la possibilité d’obtenir rapidement la référence bibliographique
                    de la page trouvée, ainsi que d’ouvrir la page correspondant à une référence. </span><a href="#">Plus d’informations <i class="fas fa-arrow-down"></i></a></p>
            </div>
        </section>
        @if (session('error'))
            <div class="container">
                <div class="message is-danger">
                    <div class="message-body">
                        {{ session('error') }}
                    </div>
                </div>
            </div>
        @endif
        @if (!empty($reference))
            <div class="section">
                <div class="container">
                    <div class="notification is-primary content" id="reference-box">
                        <p>
                            <span>Référence :</span>
                            <input class="reference" type="text" value="{{ $reference }}." readonly style="width: {{ 47 + intval(mb_strlen($reference) - 62) }}ch">
                        </p>
                        <p>
                            <a target="_blank" class="button" href="{{ $url }}">Vérifier à l’adresse indiquée (nouvel onglet) <i class="fas fa-external-link-alt"></i></a></p>
                    </div>
                </div>
            </div>
        @endif
        @if (!empty($pages))
            <div class="section">
                <div class="container">
                    <div class="notification is-primary content" id="reference-box">
                        @if (count($pages) == 1 && empty($messages))
                            <p>La version numérisée de la <input class="reference" type="text" value="{{ $pages[0]['reference'] }}" style="width: {{ 47 + intval(mb_strlen($pages[0]['reference']) - 64) }}ch"> se trouve à l’adresse <a href="{{ $pages[0]['url'] }}">{{ $pages[0]['url'] }}</a>.</p>
                            <p><a target="_blank" class="button" href="{{ $pages[0]['url'] }}">Ouvrir la page (nouvel onglet) <i class="fas fa-external-link-alt"></i></a></p>
                        @else
                            <p>Votre requête était ambiguë.
                            @if (count($pages) == 1)
                                La page suivante a été trouvée : <a href="{{ $pages[0]['url'] }}">{!! $pages[0]['reference'] !!} <i class="fas fa-external-link-alt"></i></a></p>
                            @else
                                Les références suivantes ont été trouvées :</p>
                                <ul>
                                    @foreach ($pages as $page)
                                        <li>{!! $page['input'] !!} : <a href="{{ $page['url'] }}">{!! $page['reference'] !!} <i class="fas fa-external-link-alt"></i></a></li>
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
                            <label class="label" for="url">Adresse URL de la page numérisée sur e-periodica</label>
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
                            <label class="label" for="dt" title="Veuillez compléter ce champ."><span class="required">Année</span> ou date</label>
                            <div class="control">
                                <input class="input {{ $errors->has('dt') ? 'is-danger' : '' }}" name="dt" id="dt" type="text" placeholder="1.2.1887" value="{{ old('dt') }}">
                            </div>
                            @include('field-error', ['field' => 'dt'])
                        </div>
                        <div class="field column">
                            <label class="label" for="n">Cahier</label>
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
                    <p>Technique : Site construit avec <a href="https://laravel.com/">Laravel</a>. Style basé sur <a href="https://bulma.io/">Bulma</a>. Code source disponible sur <a href="#"> <i class="fab fa-github"> </i> Github</a>.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
