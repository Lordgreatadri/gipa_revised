# GIPA Assistant

The assistant is on the public site and on every signed-in workspace page, including the investor dashboard. The control is the green button at the bottom right, with the label **Ask GIPA**. It opens a panel. The same panel is available to a visitor, an investor, and a member of staff. What it is allowed to say depends on who is signed in.

## What it will answer

It answers from the platform that exists today:

- how to register as an investor
- e-mail verification and the phone one-time code
- the business profile that must be saved before opportunity pages open
- office hours, including whether the desk is open now in Africa/Accra
- the published contact e-mail, telephone, and office
- how many regions, districts, and active sectors are on the platform
- the rule for who may see an opportunity list

A visitor who asks for opportunities is told to register, verify e-mail and phone, and save a business profile. Opportunity titles are not included. Officer phone numbers and officer e-mail addresses are not included.

A verified investor who has saved a business profile can be shown approved and active titles, with the amount and the district. Drafts and records still under review are left out. The contact fields on those records stay out of the reply.

Certificates, QR checks, matchmaking, readiness scores, and identity-document lists are not built. Questions about them are answered as not available. They are not in the knowledge base.

The assistant does not give personalised investment advice. When it has no verified source for a question, it says so and points to the contact page.

## How an answer is built

Each question is cleaned, length-limited, and checked for an attempt to override the rules. A flagged question receives a short deflection and is not given tools or documents.

Otherwise the current question is matched to a small set of tools. Tools read live platform data. They do not invent:

| Tool | What it reads |
| --- | --- |
| Investor journey | Registration, e-mail verification, phone verification, and the business profile |
| Office hours | The same desk clock as the contact page |
| Contact channels | The published e-mail, telephone, and office |
| Catalogue | Region and district counts, and active sector names |
| Active opportunities | The access rule for a visitor. Approved and active titles for a verified investor with a profile |
| Unavailable capabilities | Certificates, QR, matchmaking, readiness, and KYC |

A published knowledge document can also be retrieved when the question is about the platform rather than a live fact. Retrieval uses the current question. A short follow-up such as "what about the phone?" may include the previous question so the search still has a subject. Tools always see only the question just asked, so an office-hours follow-up does not repeat an earlier opportunity answer.

The default engine composes the reply from those tool results. If no tool matches, it quotes the retrieved document. It does not paraphrase, so it cannot add a fee, a certificate, or a record that was not supplied.

## Who counts as an investor

The investor audience is used only when the signed-in user is an investor, the e-mail is verified, the phone is verified, and a business profile exists. Guests and staff stay on the public audience. That keeps opportunity titles out of a public or staff conversation.

## Conversations

A guest conversation is resumed only when the browser still holds the same session. Another visitor who presents the conversation id is given a new conversation. A signed-in conversation belongs to that user. The question text is stored on the conversation. The application log records the conversation id, the engine, whether the reply was grounded, and which tools ran. It does not record the question body. The visitor address is stored only as a hash.

Messages are limited to 15 in a minute per visitor. A faster burst asks the person to wait.

## Knowledge the staff maintain

Six documents are seeded: about the platform, the investor journey, contact and hours, what is not available, browsing opportunities, and account privacy. Browsing opportunities is marked for the investor audience, so it is not retrieved for a guest.

Staff with assistant knowledge permission edit these at `/admin/assistant/knowledge`. A document can be published or unpublished, limited to the public or investor audience, and reindexed. Super Administrator and Content / Data Manager receive that permission. A district officer does not.

## Switching on OpenAI later

The engine is selected in the environment. The default is offline and needs no external account.

```
ASSISTANT_DRIVER=offline
```

When the client is ready:

```
ASSISTANT_DRIVER=openai
OPENAI_API_KEY=...
OPENAI_CHAT_MODEL=gpt-4o-mini
```

The hosted model receives the same tools, the same retrieved passages, and a system instruction that forbids inventing fees, contacts, certificates, or opportunity records. If the key is empty, the site stays on the offline engine, so a missing key cannot take the assistant down. Embeddings stay local. Turning on OpenAI does not require a reindex.

## Offline use

The assistant needs the network to answer. The installable site caches only a static offline page. Investor pages, admin pages, and the chat request are not stored for offline use, and the offline page does not accept a message.
