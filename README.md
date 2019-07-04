# WordPressVuxenutbildningen

###Versionsnoteringar
Utveckla i dev gren. Sammanfoga till master vid ny release och uppdatera versionsnummer enligt nedan struktur.

*Större ändringar . Antal ändringar och nya funktioner . Antal åtgärdade buggar*

####1.19.1
#####Ändringsbegäran
* Omskrivning av excerpt funktionen som används för nyhetslistning.

####1.19.0
#####Ändringsbegäran
* Tagit bort kurslistning och import från Alvis.

####1.18.1
#####Bugg
* Åtgärdat ett fel i samband med datumjämförelse mot sista ansökningsdag vid import.

####1.18.0
#####Ändring
* Ändrat cdn länk till ssl.

####1.17.2
#####Bugg
* Åtgärdat en bugg som skapade nollresultat vid sökning.

####1.17.1
#####Bugg
* Åtgärd av "Visa mer" som inte expanderade innehållet.

####1.17.0
#####Ändringsbegäran
* Ändring av sökväg till xml fil för import. Sökväg till importfil anges under "Inställningar > Kursimport".
* Borttagning av konstanterna SK_IS_DEV (bool) och ALVIS_XML_FILE_PATH (string, fullständig sökväg)
* Borttagning av PHPSECLIB för att kunna använda sftp. 

####1.14.6
#####Ändringsbegäran
* Alvis import kan nu göras mot en konfigurerbar fil. Ange konstanterna SK_IS_DEV (bool) och ALVIS_XML_FILE_PATH (string, fullständig sökväg)

####1.13.6
#####Ändringsbegäran
* Lagt till wp standard CSS för att visa wp galleri korrekt.
* Lagt till PHPSECLIB för att kunna använda sftp. 

####1.11.6
#####Bugg
* Buggfix när vi jämför sökbar till datum mot dagens datum, kursernas sökbarhet försvann en dag för tidigt från webbplatsen. 

####1.11.5
#####Ändringsbegäran
* Filmer har nu eget fält i kurser och läggs sist i content vid visning. Detta för att undvika att de synkas bort.
* Studieformer visas nu i bokstavsordning på kurssök.
* Inkluderade kurser ta med vid utskrift av en yrkesutbildning.
* Kampanjytan slumpar startbild i bildspel.

####1.11.6
#####Bugg
* Buggfix när vi jämför sökbar till datum mot dagens datum, kursernas sökbarhet försvann en dag för tidigt från webbplatsen. 

####1.11.5
#####Ändringsbegäran
* Filmer har nu eget fält i kurser och läggs sist i content vid visning. Detta för att undvika att de synkas bort.
* Studieformer visas nu i bokstavsordning på kurssök.
* Inkluderade kurser ta med vid utskrift av en yrkesutbildning.
* Kampanjytan slumpar startbild i bildspel.

####1.7.5
#####Bugg
* Error meddelande för bilagssidor åtgärdat.

####1.7.4
#####Ändringsbegäran
* Ny kolumn i tabell kursstarter, hämta kursstartbeskrivning från Alvis vid import.
* Skriv ut kurssida.
* Högerställd bild på enskild kurs.
* Ingen tumnagelbild på kurslistning vis sök.

####1.3.4
#####Ändringsbegäran
* Sökbara ikryssad som default
* Möjligt att filtrera på kommun - Kurslista
* Kursstarter i datumordning
* Skriv ut-funktion på sökträffarna

####1.3.3
#####Bugg
* Åtgärdat en bugg som inte raderade gammal data i post meta tabellen vid import av kurser.

####1.3.2
#####Ändringsbegäran
* Utskriftsvänliga sidor

####1.2.2
#####Ändringsbegäran
* YH kurser som finns sedan tidigare i Alvis ska inte importeras till wordpress
[https://trello.com/c/hihBrZDn/4-2-importera-ej-yh-utbildningar-fran-alvis](https://trello.com/c/hihBrZDn/4-2-importera-ej-yh-utbildningar-fran-alvis)

####1.1.2
#####Bugg
* Checkbox för Yrkeshögskolan saknades i och med att den inte längre inkluderas från Alvis vid import.

####1.1.1
#####Bugg
* Komplettering med full export av ACF fält.

####1.1.0
#####Ändringsbegäran
* Manuellt skapande av kurser för YH 
[https://trello.com/c/MGk4nta1/1-8-yh-skapa-kurser](https://trello.com/c/MGk4nta1/1-8-yh-skapa-kurser)

####1.0.0
Första relasen