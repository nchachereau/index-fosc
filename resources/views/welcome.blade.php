<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Index numérique de la Feuille officielle suisse du commerce (1883-2001)</title>
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    </head>
    <body>
        <section class="section is-medium">
            <div class="container">
                <h1 class="title is-2">Index numérique de la Feuille officielle suisse du commerce (1883-2001)</h1>
                <p class="content">Grâce à sa numérisation, la
                    <a href="https://www.e-periodica.ch/digbib/volumes?UID=sha-001"><i>Feuille officielle suisse du commerce</i> (FOSC)</a>
                    est appelée à devenir une ressource importante pour étudier l’histoire économique et sociale de la Suisse contemporaine.
                    À l’usage, il manque pourtant la possibilité d’obtenir rapidement la référence bibliographique de la page trouvée, ainsi
                    que d’ouvrir la page correspondant à une référence.
                </p>
            </div>
        </section>
        <section class="section">
            <div class="container">
                <h2 class="title is-4">Obtenir la référence d’une page</h2>
                <form method="get" action="">
                    <div class="columns">
                        <div class="field column">
                            <label class="label" for="url">Adresse URL de la page numérisée sur e-periodica</label>
                            <div class="control is-expanded">
                                <input class="input" name="url" id="url" type="text" value="" placeholder="https://www.e-periodica.ch/digbib/view?pid=sha-001:1887:5#86">
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
                <form action="">
                    <div class="columns">
                        <div class="column">
                            <label class="label required" for="dt" title="Veuillez compléter ce champ.">Année ou date</label>
                            <div class="control">
                                <input class="input" name="dt" id="dt" type="text" required placeholder="1887">
                            </div>
                        </div>
                        <div class="column">
                            <label class="label" for="n">Cahier</label>
                            <div class="control">
                                <input class="input" name="n" id="n" type="text" placeholder="10">
                            </div>
                        </div>
                        <div class="column">
                            <label class="label required" for="p" title="Veuillez compléter ce champ.">Page</label>
                            <div class="control">
                                <input class="input" name="p" id="p" type="text" required placeholder="73">
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <div class="control">
                            <input class="button is-primary" type="submit" value="Ouvrir la page">
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
