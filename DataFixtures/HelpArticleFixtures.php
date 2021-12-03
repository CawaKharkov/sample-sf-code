<?php

namespace App\DataFixtures;

use App\Entity\HelpArticle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class HelpArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $articles = [];

        $articles[] = (new HelpArticle())
            ->setChapter('Registration and verification')
            ->setTopic('What number should I use?')
            ->setSlug('what-number-should-i-use')
            ->setText('<ul> <li> You must provide your tax number of the country where you are a resident (the country where you spend more than 183 days a year). </li> <li> Depending on the country, this can be either a separate taxpayer number or a personal number, indicated in the identity document. </li> <li> In countries where an individual taxpayer number (TIN) is assigned, a separate document is usually issued in which this number is indicated. </li> <li> In other countries where the personal number is also the tax number, it is indicated in the main identity document. </li> <li> To find the number needed, check your passport, taxpayer card or ID card, which is suitable for traveling outside the country. </li> <li> If you are an EU resident, then in your passport or ID card this field will most likely be called national identification number, identification number, tax id, “personas kods”. In this case, the number will be located on the page with your photo. </li> <li> If you are a resident of a country outside the EU, then you need to find in the passport a field called: Identification number, Registration No, Record №, TIN </li> <li> If you do not find this number on the main page of the passport, leaf through it. Possibly, you have a separate page in your passport, where the information is pasted. This may be the case if you received the passport in one country and then moved to another but have not changed your passport yet. </li> <li> If the number in your document contains only numbers, then you write them all in a row, without dots, spaces, dashes. </li> <li> If the number in your document contains both numbers and letters, the letters should be in Latin alphabet. </li> <li> If the letters contain alphabetic characters other than Latin, for example, ч, ф then replace them with similar Latin characters, for example, ch, f. </li> </ul> <p> If the letters contain special national symbols or diacritical signs, for example, é, è, ç, then you use them without the special characters, for example, e, e, c. </p>');

        $articles[] = (new HelpArticle())
            ->setChapter('Documents and photos')
            ->setTopic('What identity documents do we accept?')
            ->setSlug('what-identity-documents-do-we-accept')
            ->setText('<ul> <li> We accept passports or national ID cards valid for crossing the border. </li> <li> If you live in EU countries, please make sure your ID card allows you to travel with it outside the state. </li> <li> Look carefully at your passport: at the very bottom of the photo page, there should be a field with MRZ code (this is a set of Latin letters, numbers and symbols necessary for automatic processing). If there is no such code, then most probably we will not be able to register you in our system. </li> <li> Look carefully at your passport and make sure there are no stains on the photo page, such as ink or coffee. </li> <li> Make sure the words in all fields are bright and clearly visible. In case you often use your passport, scuffs and stains may appear, which may prevent us from accepting your document. </li> <li> Your passport should not have expired. If the date of today, yesterday or even earlier is indicated as the expiry date, we will not be able to accept such a document. Please update it and try to register with a new document. </li> <li> You must have the document with you at the time of registration, as you will have to photograph it. </li> </ul> <p> Please be aware that when filling in the application, you must enter data from the passport or ID card that you will be photographing. </p>');

        $articles[] = (new HelpArticle())
            ->setChapter('Registration and verification')
            ->setTopic('How to enter the name of the organization that issued the document properly?')
            ->setSlug('how-to-enter-the-name-of-the-organization-that-issued-the-document-properly')
            ->setText('<ul> <li> If the document you upload is issued within the European Union, then this field will be called Autorite / Authority. </li> <li> If your document is issued outside the European Union, then you need to find the field with the name: issuing authority / Authority. </li> <li> If the data in this field is written in Latin, then enter it exactly as written in your document, with dots and numbers, for example: “2. Rigas pasu dala”. </li> <li> If the data in this field contains both numbers and letters, then the letters must be written in Latin. </li> <li> If non-Latin letters are used in the document (for example, Cyrillic, Greek, etc.), you should enter the text replacing them with similar Latin characters, for example instead of Ч,Ф you should write CH, F. </li> <li> If the letters contain special national symbols, for example, É, È, Ç, then they must be written without special characters - E, E, C. </li> </ul>');

        $articles[] = (new HelpArticle())
            ->setChapter('Registration and verification')
            ->setTopic('Who are politically exposed persons or their family members?')
            ->setSlug('who-are-politically-exposed-persons-or-their-family-members')
            ->setText('<ul> <li> Ministers, deputies, heads of state institutions, local governments, management of the state revenue service, management of the Central Bank, senior representatives of judicial institutions. </li> <li> Family members include wives, husbands, brothers and sisters, parents, grandparents, grandchildren. </li> </ul> <p> Please mark this item only if you are sure that it is correct. </p>');

        $articles[] = (new HelpArticle())
            ->setChapter('Documents and photos')
            ->setTopic('How to take photos properly?')
            ->setSlug('how-to-take-photos-properly')
            ->setText('<b>Documents</b> <ul> <li> Wipe the camera lens with a soft cloth so that the image does not turn out cloudy. </li> <li> Avoid glare and shadows on the document. </li> <li> It is best to illuminate the document with diffused light coming from the window that is above the document. </li> <li> It is most convenient to place the document on the window sill, taking the image from above, then the shadow of your hands will not fall on the document. </li> <li> If glare appears on the document, try slightly altering the angle between the document and the light source. </li> <li> When taking the photo, the camera should always be pointed directly at the document, not at an angle. If the document in the frame has the shape of a trapezoid, then it may not be recognized. </li> <li> If you hold the document in your hand, then avoid placing your fingers in the area next to the photo in the document. </li> <li> Do not cover the document with your fingers, even partially. </li> <li> All significant elements (photos, holograms, stamps, inscriptions, etc.) should be clearly visible. The document should be photographed in focus, if the photo turned out blurry, try again. </li> <li> Poor lighting may cause a blurred image. If there is not enough light, choose a more suitable place or time, or fix both the document and the smartphone in order to exclude the slightest movement when taking the photo. </li> </ul> <b>Selfies</b> <ul> <li> Try to take the selfie with uniform diffused light falling on you from the front. Remove bright sources of light from the face. There should also be no bright light sources behind you. </li> <li> Optimum light is obtained if you are facing the window, yet direct sunlight is not falling on you. </li> <li> The camera should be approximately at your eye - level. </li> <li> Take the selfie with the camera facing from the front, control your image. Avoid sharp and deep shadows or glare on your face. </li> <li> Remove any items covering the head, headphones, microphone. Wipe the lens of the camera with a soft cloth so that the image does not turn out blurry. </li> <li> Hold the camera exactly in front of your face. The shape of the face should not be distorted. Focus on your photo in the uploaded document. </li> <li> If your face looks narrower in the passport photo, try taking a selfie by slightly leaning toward the camera. This will even out the shape of the face and make the neck and chin more contoured. </li> <li> Make sure your hand/finger is removed from the camera area. </li> <li> Point the camera at your face and wait 2-3 seconds for the camera to fix it. </li> </ul>');

        $articles[] = (new HelpArticle())
            ->setChapter('Documents and photos')
            ->setTopic('What documents are accepted?')
            ->setSlug('what-documents-are-accepted')
            ->setText('<ul> <li> Statement from your bank account. Your name, surname, the address must be clearly visible. Make sure that the address in the bank statement is the one you indicated during registration. </li> <li> Utility bill for apartment, electricity, gas, water supply, fixed Internet, cable TV, landline telephone must be issued on your name. The address in the bill must be the one you indicated when registering with our bank. We cannot accept mobile phone bills. </li> <li> Declared address (registration), if it is indicated in your country. This can be a separate passport page with your residential address. In this case, it is necessary to photograph both the passport page with your photo and the page with the declared address (registration) so that each of them the passport number is visible. </li> <li> A letter from a state institution, for example, from the land service with the calculation of the land tax for the current year, from the social service about an assigned pension or benefit, a letter from the Ministry of Health about the preferential medical procedure, from the statistics department that you are included in the study group etc. Please note that the letter must include your first name, surname, and address that was indicated during registration, i.e. the recipient of the letter must be you, and not a member of your family who lives with you. </li> <li> Tax declaration you have filed for a partial refund of taxes paid by you, with a mark of your country\'s tax service. </li> <li> Identity card of the resident - if you work remotely and provided this document to your employer. The certificate must contain a stamp and signatures of the persons who issued the document. </li> <li> If you attach a document as a file, it must be a jpeg image. We do not accept files in other formats (for example, .doc or .xls). </li> <li> Pay attention to the date of issue of the document. We accept documents issued no earlier than 3 months ago. If your certificate was issued a long time ago, for example, a year or six months ago, we will not be able to accept it. </li> </ul>');

        foreach ($articles as $article) {
            $manager->persist($article);
        }

        $manager->flush();
    }
}
