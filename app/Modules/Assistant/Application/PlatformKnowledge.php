<?php

namespace App\Modules\Assistant\Application;

use App\Modules\Assistant\Domain\Models\AssistantDocument;

/**
 * Published facts about the platform as it works today.
 * Certificates, matchmaking, readiness scores and identity-document upload
 * are described only as not available.
 */
final class PlatformKnowledge
{
    /**
     * @return list<array{slug: string, title: string, category: string, audience: string, summary: string, body: string}>
     */
    public static function documents(): array
    {
        return [
            [
                'slug' => 'about-the-platform',
                'title' => 'About the Investment Opportunities Mapping Platform',
                'category' => 'about',
                'audience' => AssistantDocument::AUDIENCE_PUBLIC,
                'summary' => 'What the public site shows and what stays behind an investor account.',
                'body' => "The Investment Opportunities Mapping Platform is an official platform of the Ghana Investment Promotion Authority. The public site introduces the platform, explains how investor access works, and accepts a general enquiry.\n\nOpportunity lists, opportunity details, map points and inquiries are not published on the public site. A verified investor who has saved a business profile reviews approved and active records inside the investor workspace.",
            ],
            [
                'slug' => 'investor-journey',
                'title' => 'Investor registration, verification and the business profile',
                'category' => 'onboarding',
                'audience' => AssistantDocument::AUDIENCE_PUBLIC,
                'summary' => 'The required steps before an investor can open the workspace.',
                'body' => "A new investor registers with a name, e-mail, phone number and password. Staff accounts are not created from the public registration form.\n\nThe e-mail address must be verified. The phone number is then confirmed with a one-time code sent by text message. After both checks, the investor must save a business profile before any other investor page opens. The menu shows only Business profile until that profile exists.\n\nThe profile records the organisation or individual, business e-mail, contact, nationality, country, currency and language. Saving the profile opens browsing. An inquiry or an expression of interest still waits until GIPA approves the profile. A material change to the organisation name or business e-mail returns an approved profile for approval again, while browsing stays open.\n\nIdentity documents are not collected yet. Changing the e-mail or the phone clears that verification and closes the workspace until it is completed again.",
            ],
            [
                'slug' => 'contact-and-hours',
                'title' => 'Contact channels and office hours',
                'category' => 'contact',
                'audience' => AssistantDocument::AUDIENCE_PUBLIC,
                'summary' => 'How to reach the team and when the office is open.',
                'body' => "General questions that are not tied to a verified opportunity go through the contact page. The page shows the published e-mail, telephone and office. The enquiry form asks for a name, e-mail, phone, subject and message, plus consent. Do not send passwords, one-time codes or payment details.\n\nThe office is open Monday to Friday from 8:00 am to 5:00 pm, Africa/Accra. It is closed on Saturday, Sunday and Ghana public holidays. The contact page shows whether the office is open or closed at the time of the visit.",
            ],
            [
                'slug' => 'what-is-not-available',
                'title' => 'Capabilities that are not available yet',
                'category' => 'limits',
                'audience' => AssistantDocument::AUDIENCE_PUBLIC,
                'summary' => 'Certificates, matchmaking and identity documents are not part of the platform yet.',
                'body' => "The platform does not issue certificates and does not verify a certificate by QR code. Do not describe a certificate as active, suspended or revocable.\n\nThe platform does not calculate a matchmaking score, a readiness score, jobs created, or infrastructure quality.\n\nIdentity-document upload is not available. The business profile is the current onboarding record. Further engagement states beyond a submitted inquiry or a submitted expression of interest are not recorded.",
            ],
            [
                'slug' => 'browsing-opportunities',
                'title' => 'How a verified investor reviews opportunities',
                'category' => 'opportunities',
                'audience' => AssistantDocument::AUDIENCE_INVESTOR,
                'summary' => 'What an investor sees after the business profile is saved.',
                'body' => "After the business profile is saved, the investor can open the dashboard, opportunities, the investment map, engagement, account and notifications.\n\nThe opportunity list contains approved and active records only. Drafts, records waiting for approval, completed records and cancelled records are not listed. Each card shows the sector, a short description, the district and region, the enterprise type and the investment amount in its own currency. The record does not show the officer phone number or e-mail.\n\nAn inquiry and an expression of interest are separate. Each can be sent once for an opportunity, and only when the business profile is approved and active and the opportunity is active. The recorded state is submitted. A message on that engagement does not change the state.",
            ],
            [
                'slug' => 'account-and-privacy',
                'title' => 'Account, privacy and what the assistant will not reveal',
                'category' => 'account',
                'audience' => AssistantDocument::AUDIENCE_PUBLIC,
                'summary' => 'Account changes and the limits of this assistant.',
                'body' => "The account page holds the investor name, e-mail, phone and password. It is separate from the business profile and it opens only after the profile is saved. A password change asks for the current password.\n\nThis assistant answers from published platform information and from live tools that respect the same access rules as the site. It does not reveal passwords, one-time codes, staff contact details on an opportunity, draft records, or another person's enquiry.",
            ],
        ];
    }
}
