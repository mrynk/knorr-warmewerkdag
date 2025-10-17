<mjml lang="nl">
    <mj-body background-color="#000000">
        <mj-section background-color="#2f6830">
            <mj-column>

                <mj-image width="320px" src="{{ url('/static/warm-title.png') }}"></mj-image>

                <mj-divider border-color="#F8BA00"></mj-divider>

                <mj-text font-size="20px" color="#FFFFFF" align="justify" line-height="1.5">
                    <h1>Gefeliciteerd!</h1>
                    <p>Je hebt een {{ $reward['title'] }}* gewonnen!</p>
                </mj-text>

                <mj-image width="160px" src="{{ url('/static/rewards/' . $entry->reward->name . '.png') }}"></mj-image>

            </mj-column>
        </mj-section>
        <mj-section background-url="{{ url('/static/mail-bg.jpg') }}" background-size="100% 100%"
            background-repeat="no-repeat">
            <mj-column>
                <mj-text font-size="16px" color="#FFFFFF" align="justify" line-height="1.5">

                    <p>Wat leuk dat je meedoet aan de soepactie 'Warm je werkdag op'. Lees om je prijs te ontvangen
                        onderstaande tekst goed door!</p>
                    <p>Beantwoord deze mail voor <strong>1 december 2025</strong> met je naam en volledige adres
                        <strong>(straat
                            + huisnr, postcode +
                            woonplaats)</strong> en we sturen de prijs in week 50 naar je op!
                    </p>
                    <p>Psst... heb je al onze andere wereldse smaken geprobeerd? Elke soep = een nieuwe winkans. Warm je
                        werkdag nóg vaker op!</p>
                    <p>Veel plezier met je prijs!<br />
                        Team Warmewerkdag</p>

                    <p style="font-size: 12px; text-align: center;">*Aan de kleur van de prijs die je ontvangt kun je
                        geen rechten ontlenen.
                    </p>
                </mj-text>

                <mj-divider border-color="#F8BA00"></mj-divider>

            </mj-column>
        </mj-section>
    </mj-body>
</mjml>