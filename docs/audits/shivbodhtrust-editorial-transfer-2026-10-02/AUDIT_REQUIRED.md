# AUDIT REQUIRED — ShivBodh Trust editorial transfer

Status: **Draft / cross-check only**

This package preserves the repository-side implementation that was removed from `shivbodhtrust.org` because it belongs with the Sampreshan editorial platform.

## Scope

- Adiguru editorial page/navigation mapping
- Peetham page template and data helper
- Dharmasevaka and related editorial route inventory
- Related About/FAQ source excerpts

## Mandatory review before adoption

1. Verify every title, name, affiliation, mahavakya and destination URL.
2. Separate editorial content from puja/booking UI. No booking box or service CTA may be imported.
3. Export the original WordPress page content and media after a verified backup; database content is not present in the source repository.
4. Implement in `buddyboss-theme-child/` only after review in a separate production PR.
5. Do not copy this audit folder into `public_html/` and do not deploy it as a theme.

## Provenance

- Source repository: `Avikalp-Shukla/shivboshtrust.org`
- Source commit: `e6f3ab6dd0ae96f09b58bf22ed1ee2b417d40ec2`
- Destination base commit: `c7d1b5bd7c1abd5618bb1412a65718377d712db6`
- Prepared: 2026-10-02 (Asia/Calcutta)
