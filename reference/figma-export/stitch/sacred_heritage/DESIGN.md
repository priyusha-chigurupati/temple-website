# Design System Specification: The Sacred Modernist

## 1. Overview & Creative North Star
The Creative North Star for this design system is **"The Digital Sanctum."** 

Unlike generic religious portals that feel cluttered or dated, this system treats the interface as a high-end editorial gallery. We move beyond the "template" look by utilizing intentional asymmetry, expansive breathing room, and a sophisticated layering of warm, cream-based neutrals. The goal is to evoke a sense of spiritual tranquility through "Soft Minimalism"—where tradition (Saffron and Gold) meets modern precision (Manrope typography and Tonal Depth).

We challenge the rigid grid by allowing high-quality imagery to break container boundaries and using overlapping typography to create a sense of three-dimensional space.

---

## 2. Colors & Surface Philosophy
The palette is rooted in the earth and the divine. We use the Material Design 3 logic but elevate it with specific execution rules to ensure a premium feel.

### The Palette
- **Primary (`#a04100`):** Deep Saffron. Use for moments of high spiritual significance and primary actions.
- **Secondary (`#735c00`):** Muted Gold. Used for accents, iconography, and subtle ornamentation.
- **Background (`#fff8ef`):** A warm, high-end cream that prevents the "clinical" feel of pure white.
- **On-Surface (`#1e1b13`):** Charcoal. High-contrast for legibility but softer than pure black.

### The "No-Line" Rule
To maintain a high-fidelity editorial aesthetic, **1px solid borders are prohibited for sectioning.** 
- Boundaries must be defined solely through background color shifts. 
- Example: A section using `surface-container-low` (`#fbf3e4`) sitting directly on a `surface` background (`#fff8ef`).

### Signature Textures & Glassmorphism
- **The Golden Glow:** Use a linear gradient transitioning from `primary` (`#a04100`) to `primary-container` (`#ff9762`) at a 135-degree angle for main Hero CTAs.
- **The Frosted Veil:** For floating navigation or sticky headers, use `surface` at 80% opacity with a `20px` backdrop-blur. This allows the vibrant colors of temple imagery to bleed through softly, grounding the UI in the content.

---

## 3. Typography
The typographic pairing is a dialogue between the ancestral and the contemporary.

*   **Headings (Noto Serif):** Represents the weight of tradition. 
    *   *Display-LG (`3.5rem`):* Use for hero statements with `-0.02em` letter spacing to feel "locked" and intentional.
    *   *Headline-MD (`1.75rem`):* Use for section titles, always in `on-surface`.
*   **Body & UI (Manrope):** Represents modern accessibility. 
    *   *Body-LG (`1rem`):* Optimized for long-form reading about temple history.
    *   *Label-MD (`0.75rem`):* Used for metadata, all-caps with `0.05em` tracking for a premium "gallery" feel.

---

## 4. Elevation & Depth: The Layering Principle
We reject standard drop shadows in favor of **Tonal Layering**.

*   **The Stack:** 
    1.  Base: `surface` (`#fff8ef`)
    2.  Section: `surface-container-low` (`#fbf3e4`)
    3.  Card: `surface-container-lowest` (`#ffffff`)
*   **Ambient Shadows:** If a card requires a "lift" (e.g., on hover), use a shadow tinted with the brand color: `rgba(160, 65, 0, 0.08)` with a `40px` blur and `12px` Y-offset.
*   **The Ghost Border:** If a container needs definition against a similar tone, use `outline-variant` (`#dbc2b0`) at **15% opacity**. This creates a whisper of a boundary rather than a hard edge.

---

## 5. Components

### Navigation & Headers
*   **The Ethereal Sticky Header:** Uses the "Frosted Veil" glassmorphism. Navigation links use `label-md` in `on-surface`. The active state is indicated by a `2px` saffron dot below the text, not an underline.
*   **Primary CTA:** Roundedness `sm` (`0.125rem`) for a sharp, architectural look. Saffron background with white text.

### Cards (Events & Blogs)
*   **The Editorial Card:** Strictly no borders. Use `surface-container-highest` for the image container and `surface-container-lowest` for the content area. 
*   **Spacing:** Use `spacing-6` (`2rem`) for internal padding to ensure the content feels "exhibited" rather than "packed."
*   **Imagery:** Images within cards should have a subtle `0.5s` scale-up transform on hover.

### Forms & Inputs
*   **Fields:** Use the "Ghost Border" fallback. Labels should be `body-sm` in `on-surface-variant`. 
*   **Focus State:** Transition the border to `secondary` (Gold) at 100% opacity with a soft `4px` outer glow.

### Decorative Elements
*   **The "Spiritual Divider":** Instead of a line, use a `1px` tall gradient line that fades from transparent -> `secondary_fixed` (`#ffe088`) -> transparent.

---

## 6. Do's and Don'ts

### Do
*   **Do** use asymmetrical layouts. For example, place a `headline-lg` on the left and a `body-lg` paragraph shifted to the right grid columns.
*   **Do** use large amounts of `spacing-20` (`7rem`) between major homepage sections to create a sense of "sacred space."
*   **Do** use high-resolution photography featuring macro details (textures of stone, silk, or flowers) to create emotional depth.

### Don't
*   **Don't** use standard `000000` black for text. It breaks the warmth of the cream background. Use `on-surface` (`#1e1b13`).
*   **Don't** use fully rounded (pill) buttons. The system's "Roundedness Scale" suggests `sm` (`0.125rem`) or `none` to maintain a formal, temple-like structure.
*   **Don't** use icons from different libraries. Use thin-stroke, elegant icons that match the `outline` weight (`#887364`).

### Accessibility Note
Ensure that all saffron-colored text on cream backgrounds is checked for a 4.5:1 contrast ratio. If necessary, use `on-primary-container` (`#762e00`) for smaller text elements to maintain readability without losing the brand hue.