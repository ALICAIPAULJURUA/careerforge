# CareerForge — Future AI Architecture (Boundary Only — Not Built Now)

> Source: CareerForge Complete Technical Architecture v1.0. Companion documents: see /requirements folder for product requirements

## 11. Future AI Architecture (Boundary Only — Not Built Now)
```
interface AiAssistantInterface {
    public function suggestRewrite(string $originalText, string $context): string;
    public function extractKeywords(string $jobDescriptionText): array;
}

class AnthropicAiAssistant implements AiAssistantInterface { ... }
// or OpenAiAssistant, etc. — swappable, never referenced directly outside this boundary
```

**Design rules for when this is built:**
- No controller, Action, or Livewire component ever talks to an AI SDK directly — everything goes through `AiAssistantInterface`, resolved via the container, exactly like `PdfGeneratorInterface`.
- Only the **minimum necessary text** is sent per call (e.g., one achievement's text, or the pasted job description) — never the user's full profile — per NFR (AI Data Privacy) in the Requirements Package.
- Every AI response is treated as a **suggestion**, stored nowhere permanent until the user explicitly accepts it (a `resume_overrides` row or a direct master-record edit, going through the normal validated write path — the AI boundary never writes to the database itself).
- `JobDescriptionAnalyzerService` (FR-034–036, Should tier) is designed to work **without** this interface at all for its MVP version (simple keyword extraction/matching, per Requirements Package item #9) — the AI interface is only needed if/when that analysis is upgraded to NLP-based matching, or when summary/achievement rewriting (FR-040) is built.

This boundary means adding AI features later is additive (new class implementing an existing interface + new UI affordances to trigger/accept suggestions), not a refactor of the resume or profile logic.

---
