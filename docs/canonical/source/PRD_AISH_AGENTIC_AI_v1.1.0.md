# PRODUCT REQUIREMENT DOCUMENT

# AISH AGENTIC AI

**Dokumen:** Product Requirement Document  
**Versi:** 1.1.0  
**Status:** STEP 2 BASELINE COMPLETE — PRODUCT IMPLEMENTATION NOT STARTED  
**Tanggal:** 13 Juli 2026  
**Timezone:** Asia/Makassar  
**Pemilik produk:** Aish Tech Solution  
**Sumber kanonik:** Master Source Aish Agentic AI v2.2.0  
**Target rilis awal:** MVP Pilot  
**Pilot tenant utama:** Klinik Gigi Daengtisia  
**Target pasar awal:** Indonesia  
**Target jangka panjang:** Global market

---

# 1. TUJUAN DOKUMEN

PRD ini menerjemahkan visi, scope, governance, dan roadmap Aish Agentic AI menjadi persyaratan produk yang dapat digunakan oleh Product Owner, Business Analyst, UI/UX Designer, Engineer, AI Engineer, QA, DevOps, Security Reviewer, dan coding agent.

Dokumen ini menjadi baseline untuk:

- Menentukan produk yang harus dibangun pada MVP.
- Menjaga scope agar tidak melebar sebelum fondasi stabil.
- Menetapkan workflow manual, semi-otomatis, dan berbasis AI.
- Menetapkan persyaratan multi-tenant dan multi-cabang.
- Menentukan acceptance criteria dan release gate.
- Menjadi dasar penyusunan persona pilot, arsitektur repository, roadmap sprint, database schema, dan wireframe.

PRD ini tidak menyatakan bahwa aplikasi telah dikembangkan, diuji, di-deploy, atau siap dipakai. Status saat ini hanya menandakan bahwa baseline persyaratan produk telah disusun.

---

# 2. RINGKASAN PRODUK

Aish Agentic AI adalah platform SaaS multi-tenant untuk mengelola pengalaman pelanggan dan reputasi bisnis melalui:

- CSAT, NPS, dan CES.
- Survey builder dan survey campaign.
- Feedback inbox.
- Customer recovery ticket.
- Google Business Profile dan Google Review management.
- AI sentiment, topic, severity, summary, dan response draft.
- Human approval untuk tindakan publik atau berisiko.
- Knowledge base tenant-scoped.
- Multi-branch analytics.
- Subscription, usage metering, audit, dan platform administration.

Aish Agentic AI harus tetap berguna ketika layanan AI tidak tersedia. Pengumpulan feedback, ticketing, assignment, approval, publikasi manual, dan audit tidak boleh bergantung sepenuhnya pada AI.

---

# 3. VISI DAN POSITIONING

## 3.1 Visi

Membangun operating platform global untuk customer experience, customer recovery, dan online reputation yang aman, terukur, dapat diaudit, dan dapat digunakan oleh UMKM maupun enterprise.

## 3.2 Positioning

Aish Agentic AI diposisikan sebagai:

> **Agentic AI Customer Experience and Reputation Operating Platform**

Produk bukan sekadar aplikasi survei, dashboard review, chatbot, atau generator balasan. Produk harus mengelola siklus berikut:

```text
Service completed
    ↓
Feedback collection
    ↓
AI-assisted analysis
    ↓
Risk and topic classification
    ↓
Customer recovery
    ↓
Review management
    ↓
Operational insight
    ↓
Continuous improvement
```

---

# 4. MASALAH PENGGUNA

Aish Agentic AI dirancang untuk menyelesaikan masalah berikut:

1. Feedback tersebar di banyak kanal dan tidak terpusat.
2. Bisnis tidak memiliki metrik CSAT, NPS, atau CES yang konsisten.
3. Komplain tidak memiliki PIC, SLA, histori, atau eskalasi.
4. Review Google terlambat atau tidak dibalas.
5. Balasan review tidak konsisten dan berpotensi membuka data pribadi.
6. Pemilik kesulitan membandingkan performa cabang.
7. Bisnis tidak memiliki root cause analysis dan rekomendasi operasional.
8. Penggunaan AI tidak memiliki approval, audit, guardrail, tracing, dan cost control.
9. Tidak ada hubungan yang jelas antara transaksi, feedback, ticket, dan review.
10. Status sistem eksternal sering tidak ditampilkan secara jujur.

---

# 5. SASARAN PRODUK

## 5.1 Sasaran MVP

MVP harus memungkinkan tenant untuk:

1. Mengelola organisasi, cabang, pengguna, role, dan permission.
2. Membuat serta mempublikasikan survei CSAT, NPS, dan CES.
3. Mengirim undangan melalui QR, link, WhatsApp link, dan email.
4. Mengumpulkan feedback dan melihatnya dalam feedback inbox.
5. Membuat recovery ticket dengan assignment, SLA, dan escalation.
6. Menganalisis feedback menggunakan AI secara terstruktur dan dapat diaudit.
7. Menghubungkan Google Business Profile dan memetakan lokasi ke cabang.
8. Menyinkronkan Google Review.
9. Membuat draft balasan review berbasis AI.
10. Melakukan human approval sebelum publikasi.
11. Melihat owner dashboard dan branch dashboard.
12. Mengelola subscription dasar, entitlement, dan usage metering.
13. Menyediakan platform admin, audit log, observability dasar, backup, dan restore.

## 5.2 Sasaran bisnis

- Memvalidasi kebutuhan pasar melalui pilot nyata.
- Membuktikan bahwa produk dapat digunakan lintas industri.
- Mengurangi waktu respons terhadap feedback negatif.
- Meningkatkan response rate dan review response rate.
- Menyediakan data yang cukup untuk menentukan pricing final.
- Mengukur biaya AI per tenant dan per workflow.

## 5.3 Non-goals MVP

MVP tidak mencakup:

- Fully autonomous complaint handling.
- Auto-refund atau auto-compensation.
- Auto-publish semua review reply.
- Voice agent atau full AI call center.
- Semua social media sekaligus.
- Complex no-code workflow builder.
- Integration marketplace.
- Full customer data platform.
- Loyalty dan full marketing automation.
- Dedicated mobile application.
- Advanced predictive analytics.

---

# 6. TARGET PASAR DAN PILOT

## 6.1 Prioritas pasar

1. Klinik dan healthcare service.
2. Restoran dan hospitality.
3. Retail multi-cabang.
4. Franchise.
5. Professional services.

## 6.2 Pilot tenant utama

Klinik Gigi Daengtisia menjadi pilot tenant utama untuk memvalidasi:

- Survey pasca-kunjungan.
- Feedback mengenai waktu tunggu, keramahan, kebersihan, komunikasi, dokter, fasilitas, dan pembayaran.
- Customer recovery.
- Multi-branch comparison.
- Privacy-safe Google Review response.

Core product tidak boleh hard-coded untuk klinik. Seluruh istilah, kategori, workflow, dan knowledge harus dapat dikonfigurasi untuk industri lain.

---

# 7. PERSONA UTAMA

## 7.1 Business Owner

**Tujuan:** Memahami performa bisnis dan cabang.  
**Kebutuhan utama:** Executive summary, branch ranking, rating trend, top complaint, SLA, recovery rate, dan rekomendasi.

## 7.2 Corporate Admin

**Tujuan:** Mengelola tenant dan konfigurasi organisasi.  
**Kebutuhan utama:** Cabang, user, role, survey, integration, subscription, dan permission.

## 7.3 Regional Manager

**Tujuan:** Mengawasi cabang dalam wilayah tertentu.  
**Kebutuhan utama:** Regional comparison, overdue tickets, escalation, dan rating regional.

## 7.4 Branch Manager

**Tujuan:** Menindaklanjuti feedback dan review cabang.  
**Kebutuhan utama:** Action queue, feedback cabang, ticket assignment, SLA, dan review pending approval.

## 7.5 Customer Experience Manager

**Tujuan:** Mengelola complaint triage dan recovery.  
**Kebutuhan utama:** Feedback inbox, severity, assignment, escalation, resolution, dan root cause.

## 7.6 Customer Service

**Tujuan:** Menangani tugas dan komunikasi pelanggan.  
**Kebutuhan utama:** Assigned tickets, response draft, customer timeline, checklist, dan notes.

## 7.7 Reputation Manager

**Tujuan:** Mengelola Google Review secara konsisten dan aman.  
**Kebutuhan utama:** Review inbox, AI draft, approval, publish, response rate, dan publication status.

## 7.8 Platform Admin

**Tujuan:** Menjaga kesehatan SaaS.  
**Kebutuhan utama:** Tenant health, subscription, integration health, agent failure, cost, incident, dan support tools.

---

# 8. JOBS TO BE DONE

1. Saat layanan selesai, bisnis ingin mengirim survei tanpa proses manual berulang.
2. Saat pelanggan memberi feedback, tim ingin mengetahui topik, sentimen, severity, dan tindakan yang disarankan.
3. Saat feedback berisiko tinggi masuk, manajer ingin ticket, PIC, SLA, dan eskalasi tercipta dengan cepat.
4. Saat review Google masuk, reputation manager ingin draft aman yang sesuai brand voice.
5. Saat draft siap, approver ingin melihat alasan, confidence, guardrail, dan knowledge yang digunakan sebelum menyetujui.
6. Saat tindakan eksternal dilakukan, pengguna ingin melihat status sebenarnya, bukan status optimistis palsu.
7. Saat owner membuka dashboard, owner ingin melihat prioritas masalah dan tindakan yang disarankan.
8. Saat platform admin menyelidiki insiden, admin ingin melihat trace, audit, retry, error, dan cost tanpa mengakses data tenant secara sembarangan.

---

# 9. PRINSIP DAN BATASAN PRODUK

1. **Multi-tenant by design.** Seluruh data tenant wajib memiliki tenant context.
2. **Multi-branch by design.** Data operasional harus dapat dibatasi menurut cabang atau region.
3. **Human-in-the-loop.** Tindakan publik atau berisiko memerlukan approval.
4. **Workflow manual tetap tersedia.** AI tidak menjadi single point of failure.
5. **Privacy by design.** Data pribadi, medis, finansial, dan internal diminimalkan.
6. **Auditability.** Tindakan penting harus dapat ditelusuri.
7. **Truthful states.** Status eksternal hanya sukses setelah terverifikasi.
8. **Reliable before autonomous.** Automation bertambah hanya setelah workflow dasar stabil.
9. **Policy-safe.** Review gating dan manipulasi review dilarang.
10. **Evidence-based completion.** Selesai harus disertai test, deployment, runtime verification, dan evidence sesuai gate.

---

# 10. SCOPE FUNGSIONAL MVP

## 10.1 Authentication dan identity

### Requirements

- FR-IAM-001: Pengguna dapat login, logout, dan reset password.
- FR-IAM-002: Email verification tersedia untuk akun baru.
- FR-IAM-003: MFA dapat diaktifkan, minimal untuk privileged roles.
- FR-IAM-004: Sistem mencatat login success, failure, logout, reset, lockout, dan perubahan kredensial.
- FR-IAM-005: Session management dapat mencabut sesi aktif.
- FR-IAM-006: Rate limiting dan account lockout diterapkan.

### Acceptance criteria

- Pengguna tidak aktif atau suspended tidak dapat login.
- Login lintas tenant tidak memberikan akses data tenant lain.
- Aktivitas authentication tercatat tanpa menyimpan password atau secret.

## 10.2 Tenant, branch, region, dan brand

- FR-TEN-001: Platform admin dapat membuat, menangguhkan, mengaktifkan kembali, dan mengarsipkan tenant.
- FR-TEN-002: Tenant dapat mengelola brand, region, dan branch.
- FR-TEN-003: Tenant dapat menyimpan timezone, language, industry, contact, operating hours, dan branding.
- FR-TEN-004: Branch dapat memiliki manager, SLA, survey, Google location mapping, dan knowledge scope.
- FR-TEN-005: Suspended tenant tidak dapat melakukan mutation operasional tetapi histori tetap dapat dipertahankan sesuai policy.

## 10.3 User, role, permission, dan scope

- FR-RBAC-001: Tenant admin dapat mengundang pengguna.
- FR-RBAC-002: Pengguna dapat memiliki role dan custom permission.
- FR-RBAC-003: Akses dapat dibatasi berdasarkan tenant, region, dan branch.
- FR-RBAC-004: Perubahan role atau permission tercatat pada audit log.
- FR-RBAC-005: Platform role dipisahkan secara ketat dari tenant role.
- FR-RBAC-006: Platform support tidak otomatis memiliki akses isi data tenant.

## 10.4 Survey builder

- FR-SUR-001: Pengguna berizin dapat membuat survei draft.
- FR-SUR-002: Survei mendukung CSAT, NPS, CES, star rating, emoji, pilihan, checkbox, yes/no, dropdown, short text, long text, dan consent.
- FR-SUR-003: Survei memiliki versioning dan published version tidak berubah diam-diam.
- FR-SUR-004: Survei dapat dipreview sebelum publish.
- FR-SUR-005: Survei dapat di-pause dan di-archive.
- FR-SUR-006: Survei mendukung branding dan minimal Bahasa Indonesia serta English-ready structure.
- FR-SUR-007: Anonymous dan identified mode harus dibedakan secara eksplisit.

## 10.5 Survey campaign dan invitation

- FR-CAM-001: Campaign memiliki nama, trigger, audience, branch filter, delay, channel, frequency limit, start, end, dan reminder.
- FR-CAM-002: Sistem dapat membuat QR survey dan public link.
- FR-CAM-003: Sistem dapat membuat unique invitation token dengan expiration.
- FR-CAM-004: Undangan dapat dikirim melalui email dan link WhatsApp.
- FR-CAM-005: Delivery, open, completion, failure, reminder, dan expiration status dicatat.
- FR-CAM-006: Retry pengiriman tidak boleh membuat undangan ganda yang tidak terkontrol.
- FR-CAM-007: Consent dan communication preference harus dihormati.

## 10.6 Survey response dan metric calculation

- FR-RES-001: Response disimpan terhadap survey version yang tepat.
- FR-RES-002: CSAT, NPS, dan CES dihitung dengan formula yang terdokumentasi.
- FR-RES-003: Sistem menolak submission tidak valid atau token kedaluwarsa sesuai rule.
- FR-RES-004: Duplicate atau repeated submission ditangani secara konfigurabel.
- FR-RES-005: Response dapat dikaitkan dengan customer, transaction, service event, branch, campaign, dan invitation.

## 10.7 Feedback inbox

- FR-FBK-001: Feedback inbox mendukung search, filter, sort, date range, rating, sentiment, topic, severity, branch, campaign, status, dan assignee.
- FR-FBK-002: Feedback detail menampilkan source, response, customer context yang diizinkan, transaction reference, timeline, notes, attachment, AI result, dan related ticket.
- FR-FBK-003: Pengguna dapat menambah internal notes dan tags.
- FR-FBK-004: Export hanya tersedia bagi permission yang tepat dan selalu diaudit.
- FR-FBK-005: Bulk action harus tenant-scoped dan branch-scoped.
- FR-FBK-006: Sistem tetap berfungsi ketika AI analysis pending atau failed.

## 10.8 Recovery ticket

- FR-TKT-001: Ticket dapat dibuat otomatis melalui rule atau manual oleh user.
- FR-TKT-002: Ticket memiliki number, source, category, severity, priority, status, assignee, team, SLA, dan branch.
- FR-TKT-003: Status minimum: New, Triaged, Assigned, In Progress, Waiting Customer, Waiting Internal, Escalated, Resolved, Closed, Reopened, Cancelled.
- FR-TKT-004: Ticket menyimpan first response deadline dan resolution deadline.
- FR-TKT-005: Sistem mengirim reminder dan escalation berdasarkan SLA.
- FR-TKT-006: Contact attempt, result, notes, attachment, root cause, corrective action, preventive action, dan resolution dicatat.
- FR-TKT-007: Refund, discount, compensation, legal statement, dan sensitive resolution memerlukan approval sesuai policy.
- FR-TKT-008: Reopen dan close memerlukan reason.
- FR-TKT-009: Timeline tidak dapat dimanipulasi tanpa audit.

## 10.9 Basic AI analysis

- FR-AI-001: AI provider dipanggil melalui abstraction yang dapat diganti.
- FR-AI-002: Input, output, prompt version, model version, latency, token, cost, dan status dicatat.
- FR-AI-003: Output wajib terstruktur untuk sentiment, topic, severity, summary, confidence, risk, dan suggested action.
- FR-AI-004: Customer content diperlakukan sebagai untrusted input.
- FR-AI-005: PII redaction dan prompt injection guardrail dijalankan sebelum tool atau publikasi.
- FR-AI-006: AI failure tidak menghapus input dan tidak menghalangi workflow manual.
- FR-AI-007: Retry terkontrol dan idempotent.
- FR-AI-008: Human correction dapat dicatat sebagai evaluation data.
- FR-AI-009: Kill switch tersedia per tenant dan platform.

## 10.10 Google Business Profile connection

- FR-GBP-001: Tenant integration admin dapat memulai OAuth connection.
- FR-GBP-002: OAuth state, redirect URI, permission, dan token lifecycle harus divalidasi.
- FR-GBP-003: Access dan refresh token dienkripsi.
- FR-GBP-004: Account dan location dapat ditemukan sesuai izin API.
- FR-GBP-005: Google location dapat dipetakan ke branch.
- FR-GBP-006: Connection health menampilkan Connected, Expiring, Reauthorization Required, Permission Missing, Syncing, Sync Failed, atau Disconnected.
- FR-GBP-007: Tenant dapat disconnect dan menghapus credential sesuai policy.
- FR-GBP-008: Development dan production credential dipisahkan.

## 10.11 Google Review sync dan inbox

- FR-GRV-001: Review disinkronkan secara periodic dan incremental.
- FR-GRV-002: Sinkronisasi idempotent dan memiliki cursor atau last synced marker.
- FR-GRV-003: Review inbox mendukung location, branch, rating, reply status, sentiment, topic, date, dan search.
- FR-GRV-004: Review detail menampilkan review data yang diizinkan API, existing reply, sync state, AI analysis, dan publication state.
- FR-GRV-005: Sistem menangani rate limit, token expiration, partial failure, dan reauthorization.
- FR-GRV-006: Sync log dan error tidak boleh membocorkan token.

## 10.12 AI review response dan approval

- FR-RPL-001: User berizin dapat meminta AI membuat draft balasan.
- FR-RPL-002: Draft menggunakan brand voice, branch knowledge, approved templates, prohibited phrases, dan privacy rules.
- FR-RPL-003: Draft melewati privacy, policy, PII, medical data, legal admission, threat, discrimination, dan manipulation guardrail.
- FR-RPL-004: Semua draft pada MVP harus melalui human approval sebelum publish.
- FR-RPL-005: Review bintang satu, dua, atau berisiko selalu memerlukan elevated approval.
- FR-RPL-006: Approver dapat approve, reject, request changes, atau mengedit draft.
- FR-RPL-007: Human edit disimpan untuk evaluation tanpa menghapus draft awal.
- FR-RPL-008: Status publish hanya Published setelah API mengonfirmasi keberhasilan.
- FR-RPL-009: Publication failure mempertahankan error state dan menyediakan safe retry.
- FR-RPL-010: Tidak ada review gating atau conditional access ke Google Review berdasarkan rating atau sentimen.

## 10.13 Knowledge base

- FR-KB-001: Knowledge base tenant-scoped dan dapat memiliki branch scope.
- FR-KB-002: Knowledge mendukung business profile, services, FAQ, operating hours, policies, tone, templates, prohibited wording, contact, SLA, escalation, compensation authority, privacy, dan legal guidance.
- FR-KB-003: Document memiliki version, owner, status, approval, effective date, dan expiration.
- FR-KB-004: Retrieval hanya mengambil konteks minimum yang relevan.
- FR-KB-005: Retrieval log mencatat source knowledge tanpa menampilkan secret.
- FR-KB-006: Critical knowledge change memerlukan approval.

## 10.14 Dashboard dan analytics

- FR-DAS-001: Owner dashboard menampilkan CSAT, NPS, CES, rating, review volume, response rate, negative feedback, open/overdue ticket, SLA, top topics, branch ranking, recovery, dan summary.
- FR-DAS-002: Branch dashboard menampilkan metrik cabang, feedback hari ini, new reviews, unanswered reviews, tickets, overdue, top issues, dan action queue.
- FR-DAS-003: Semua metrik dapat difilter per periode dan scope pengguna.
- FR-DAS-004: Data dashboard harus berasal dari data nyata, bukan placeholder produksi.
- FR-DAS-005: Last updated dan data freshness ditampilkan.
- FR-DAS-006: Empty, loading, stale, partial, dan failure state tersedia.

## 10.15 Notification center

- FR-NOT-001: Mendukung in-app dan email pada MVP; channel lain dapat ditambahkan kemudian.
- FR-NOT-002: Rule dapat berdasarkan severity, rating, SLA, connection health, rating drop, dan complaint spike.
- FR-NOT-003: Deduplication, quiet hours, escalation chain, digest, dan rate limit tersedia.
- FR-NOT-004: Penerima tunduk pada tenant, region, branch, role, dan permission scope.

## 10.16 Subscription, entitlement, dan metering

- FR-BIL-001: Platform memiliki plan Starter, Growth, Business, dan Enterprise sebagai baseline konfigurasi.
- FR-BIL-002: Entitlement mengontrol branch, user, invitation, AI run, Google location, export, API, dan fitur lain.
- FR-BIL-003: Usage record idempotent, auditable, tenant-scoped, plan-aware, dan retry-safe.
- FR-BIL-004: Trial, active, grace, past due, suspended, cancelled, dan expired state tersedia.
- FR-BIL-005: Overage tidak boleh dihitung ganda akibat retry.
- FR-BIL-006: Billing skeleton tidak boleh menghambat pilot manual billing.

## 10.17 Platform admin console

- FR-ADM-001: Platform admin melihat tenant list, status, subscription, usage, connection health, agent failures, dan incidents.
- FR-ADM-002: Impersonation, jika dibuat, harus explicit, time-bound, consent-aware, dan diaudit.
- FR-ADM-003: Platform admin dapat suspend/reactivate tenant sesuai permission.
- FR-ADM-004: Global feature flag, plan configuration, AI kill switch, dan cost alert tersedia sesuai role.
- FR-ADM-005: Platform support tidak boleh mengubah data tenant tanpa workflow dan audit yang sah.

## 10.18 Audit dan security event

- FR-AUD-001: Audit mencatat actor, action, target, tenant, branch, timestamp, source, before/after yang aman, dan correlation ID.
- FR-AUD-002: Audit tersedia untuk role/permission, export, deletion, credential, Google publish, approval, ticket resolution, knowledge, AI override, dan admin actions.
- FR-AUD-003: Secret, token, password, dan sensitive content tidak dicatat secara plaintext.
- FR-AUD-004: Security events terpisah atau dapat dibedakan dari operational audit.

---

# 11. WORKFLOW KANONIK

## 11.1 Feedback workflow

```text
Service completed
    ↓
Campaign eligibility evaluated
    ↓
Invitation created
    ↓
Invitation delivered
    ↓
Customer submits response
    ↓
Metrics calculated
    ↓
Feedback item created
    ↓
AI analysis queued
    ↓
Rule engine evaluates risk
    ↓
Ticket created when required
    ↓
Assignment and SLA begin
    ↓
Resolution and follow-up recorded
```

## 11.2 Google Review workflow

```text
Review synchronized
    ↓
AI analysis
    ↓
Knowledge retrieval
    ↓
Draft generated
    ↓
Guardrail evaluation
    ↓
Human review
    ↓
Approval
    ↓
Publish request
    ↓
External result verification
    ↓
Published or Publication Failed
```

## 11.3 High-risk workflow

```text
Feedback or review detected
    ↓
Risk classification
    ↓
Guardrail blocks unsafe automation
    ↓
Critical ticket and escalation
    ↓
Restricted human review
    ↓
Approved private response or action
    ↓
Resolution evidence
```

---

# 12. AGENTIC AI REQUIREMENTS

## 12.1 Architecture

Aish Agentic AI menggunakan Supervisor Agent dan specialist agents:

- Feedback Intake Agent.
- Sentiment and Topic Agent.
- Severity and Risk Agent.
- Recovery Agent.
- Google Review Response Agent.
- Policy and Privacy Guardrail Agent.
- Insight Agent.
- Notification Agent.

## 12.2 Agent rules

- Supervisor membawa tenant, branch, user, permission, correlation, dan approval context.
- Agent hanya dapat menggunakan tool yang di-allowlist.
- Tool arguments harus divalidasi server-side.
- Customer content tidak boleh menentukan tool call.
- Sensitive action memerlukan approval node.
- Agent failure masuk retry terbatas atau dead-letter workflow.
- Agent tidak boleh mengklaim tool action berhasil sebelum result terverifikasi.
- Seluruh run, step, handoff, tool call, guardrail, approval, failure, token, dan cost dicatat.

## 12.3 Minimum structured output

```json
{
  "sentiment": "negative",
  "topics": ["waiting_time"],
  "severity": "medium",
  "priority": "high",
  "requires_human": true,
  "summary": "Pelanggan mengeluhkan waktu tunggu yang lama.",
  "suggested_action": "Hubungi pelanggan melalui kanal privat dan evaluasi antrian cabang.",
  "risk_codes": [],
  "confidence": 0.91
}
```

---

# 13. HUMAN APPROVAL MATRIX

Approval wajib untuk:

- Review bintang satu dan dua.
- Medical, legal, safety, fraud, discrimination, threat, atau viral risk.
- PII atau sensitive data detection.
- Refund, discount, compensation, atau data deletion.
- Legal statement atau admission of fault.
- Low-confidence AI output.
- Policy conflict.
- Repeated customer contact.
- Critical knowledge base change.
- Public reply pada MVP.

Dapat diotomatisasi lebih awal:

- Sentiment, topic, summary, severity suggestion.
- Duplicate dan spam detection.
- Internal assignment suggestion.
- SLA calculation.
- Internal reminder dan digest.
- Draft creation tanpa publikasi.

---

# 14. DATA REQUIREMENTS

## 14.1 Core entities

- Tenant, tenant settings, subscription, plan, entitlement, usage.
- Brand, region, branch, department, team.
- User, role, permission, scope.
- Customer, consent, identifier, transaction, service event.
- Survey, version, question, option, campaign, invitation, response, answer.
- Feedback, topic, tag, sentiment, AI analysis, attachment.
- Recovery ticket, assignment, comment, event, SLA, resolution, escalation.
- Google connection, account, location, mapping, review, reply, sync log.
- Knowledge base, document, chunk, version, approval.
- Agent run, step, tool call, handoff, guardrail, approval, failure, cost.
- Notification, rule, delivery.
- Integration, credential, log, webhook, delivery.
- Audit log, security event, export, deletion request.

## 14.2 Data rules

- Semua tenant-owned records memiliki `tenant_id`.
- Branch-owned records memiliki `branch_id` jika relevan.
- Foreign key, unique constraint, index, soft-delete, retention, dan archival policy ditentukan pada database design step.
- Token dan credential dienkripsi.
- PII diklasifikasikan dan diminimalkan.
- AI input dapat melalui redaction.
- Prompt dan model version dicatat.
- Queue job membawa tenant context.
- Export, deletion, dan credential access diaudit.

---

# 15. NON-FUNCTIONAL REQUIREMENTS

## 15.1 Security

- NFR-SEC-001: Tenant isolation diuji pada query, cache, queue, storage, search, export, API, webhook, AI retrieval, analytics, dan notification.
- NFR-SEC-002: TLS wajib untuk seluruh traffic production.
- NFR-SEC-003: Credential dan token dienkripsi at rest.
- NFR-SEC-004: Secret tidak boleh berada di repository atau log.
- NFR-SEC-005: CSRF, XSS, SQL injection, IDOR, SSRF yang relevan, file upload, webhook forgery, dan privilege escalation diuji.
- NFR-SEC-006: MFA wajib dipertimbangkan untuk privileged roles sebelum production.
- NFR-SEC-007: Prompt injection dan tool abuse test wajib.

## 15.2 Privacy

- NFR-PRV-001: Balasan publik tidak boleh mengungkap diagnosis, prosedur, riwayat kunjungan, kondisi kesehatan, nomor rekam medis, obat, jadwal, atau hasil pemeriksaan.
- NFR-PRV-002: Data retention dapat dikonfigurasi.
- NFR-PRV-003: Data export dan deletion memiliki permission, approval jika perlu, audit, dan evidence.
- NFR-PRV-004: Platform support access dibatasi dan diaudit.

## 15.3 Reliability

- NFR-REL-001: Queue job idempotent untuk invitation, sync, publish, metering, dan notification.
- NFR-REL-002: External API failure tidak menghasilkan false success.
- NFR-REL-003: Retry memiliki limit dan backoff.
- NFR-REL-004: Dead-letter atau failure queue tersedia untuk pekerjaan kritis.
- NFR-REL-005: Backup dijadwalkan dan restore diuji.

## 15.4 Performance

Target awal yang harus divalidasi pada architecture dan load test:

- Halaman operasional umum p95 server response target ≤ 1.5 detik pada beban pilot, di luar latency provider eksternal.
- Dashboard utama p95 target ≤ 3 detik dengan caching dan query terkontrol.
- Public survey usable pada jaringan seluler dan tidak bergantung pada JavaScript berat.
- Pekerjaan AI dan sync eksternal berjalan asynchronous bila tidak memerlukan blocking response.
- Sistem mendukung pertumbuhan tenant tanpa cross-tenant degradation yang tidak terkontrol.

Target ini merupakan baseline engineering dan dapat disesuaikan setelah benchmark.

## 15.5 Availability dan recovery

- Health check untuk application, database, Redis, queue, storage, dan integration.
- Maintenance mode dan rollback procedure tersedia.
- RPO/RTO final ditentukan sebelum production launch.
- Pilot tidak boleh dianggap production-ready sebelum restore drill lulus.

## 15.6 Accessibility dan usability

- Responsive untuk desktop dan mobile web.
- Keyboard navigation dan form label yang memadai.
- Error message dapat dipahami.
- Empty, loading, success, denied, stale, offline/failed, dan retry state tersedia.
- Tindakan berisiko memiliki confirmation dan permission check server-side.

## 15.7 Localization

- Bahasa Indonesia menjadi default pilot.
- Struktur aplikasi harus translation-ready.
- Timezone disimpan dan ditampilkan secara tenant-aware.
- Format tanggal, angka, dan mata uang dapat dikonfigurasi.

---

# 16. TRUTHFUL SYSTEM STATES

## 16.1 Connection

Connected, Expiring, Reauthorization Required, Permission Missing, Syncing, Sync Failed, Disconnected.

## 16.2 AI

Pending, Running, Completed, Requires Approval, Blocked by Guardrail, Failed, Retry Scheduled, Cancelled.

## 16.3 Reply

No Draft, Draft Generated, Under Review, Changes Requested, Approved, Publishing, Published, Publication Failed, Moderation Pending, Policy Issue, Removed.

## 16.4 Ticket

New, Triaged, Assigned, In Progress, Waiting Customer, Waiting Internal, Escalated, Resolved, Closed, Reopened, Cancelled.

Status UI harus berasal dari state persistence yang sah dan, untuk aksi eksternal, dari hasil verifikasi provider.

---

# 17. GOOGLE REVIEW POLICY REQUIREMENTS

- Semua pelanggan yang memenuhi syarat mendapat akses setara untuk memberi review.
- Aish Agentic AI tidak boleh meminta rating tertentu atau hanya mengarahkan pelanggan puas ke Google Review.
- Insentif untuk review positif, review palsu, review pembelian, dan target manipulatif dilarang.
- Feedback negatif boleh membuat recovery ticket, tetapi tidak boleh menghilangkan akses review.
- Seluruh reply MVP melalui human approval.
- Kebijakan dan API Google terbaru wajib diverifikasi ulang pada fase integrasi dan sebelum production launch.

---

# 18. INTEGRATION REQUIREMENTS

## 18.1 Prioritas MVP

1. Google Business Profile.
2. Email provider.
3. WhatsApp invitation link atau WhatsApp Business Platform bila readiness memungkinkan.
4. Public API dan webhook dasar.

## 18.2 API rules

- Versioned endpoint.
- Tenant scoping.
- Authentication dan rate limiting.
- Idempotency key untuk mutation yang sesuai.
- Pagination dan consistent error format.
- Webhook signature, retry, delivery log, dan replay protection.
- Tidak ada sensitive data pada URL atau log.

## 18.3 Minimum API candidates

```text
POST /api/v1/customers
POST /api/v1/transactions
POST /api/v1/service-events
POST /api/v1/survey-invitations
GET  /api/v1/feedback
GET  /api/v1/reviews
GET  /api/v1/recovery-tickets
POST /api/v1/webhooks
```

Endpoint final ditentukan pada API design step.

---

# 19. ANALYTICS DAN SUCCESS METRICS

## 19.1 Product adoption

- Activated tenants.
- Active branches.
- Active users.
- Time to first survey.
- Time to first completed response.
- Weekly active operational users.

## 19.2 Customer experience

- CSAT, NPS, CES.
- Invitation delivery rate.
- Response rate.
- Completion rate.
- Promoter dan detractor rate.
- Repeat complaint rate.

## 19.3 Recovery operations

- Ticket creation rate.
- First response time.
- Resolution time.
- SLA compliance.
- Escalation rate.
- Reopen rate.
- Contacted dan recovered customer rate.

## 19.4 Reputation

- Average rating.
- New review count.
- Response rate.
- Median response time.
- Unanswered review.
- Negative review rate.
- Publication failure.

## 19.5 AI quality

- Sentiment accuracy.
- Topic accuracy.
- Severity precision dan recall.
- Structured output validity.
- Human approval dan edit rate.
- PII leakage rate.
- Unsafe response rate.
- Hallucination rate.
- Tool accuracy.
- Cost dan latency.

## 19.6 Pilot success hypothesis

Pilot dianggap memiliki sinyal positif bila:

- Workflow digunakan oleh tim operasional secara berulang.
- Tidak ada cross-tenant leakage atau critical privacy incident.
- Feedback dan ticket dapat ditindaklanjuti tanpa bergantung pada engineer.
- AI menghasilkan penghematan waktu dengan edit manusia yang dapat diterima.
- Google integration stabil setelah approval dan credential tersedia.
- Biaya per tenant dapat diprediksi.
- Pilot tenant menyatakan intent untuk terus menggunakan atau membayar.

Ambang numerik final ditetapkan pada Step 2 persona dan pilot use case setelah baseline operasional tenant dikumpulkan.

---

# 20. SUBSCRIPTION BASELINE

## Starter

Satu cabang, tiga pengguna, CSAT/NPS, QR survey, basic dashboard, basic AI sentiment, dan email support.

## Growth

Hingga lima cabang, Google Review integration, AI response draft, recovery ticket, WhatsApp integration, advanced analytics, SLA, dan export.

## Business

Hingga 25 cabang, regional management, multi-level approval, API, webhook, custom SLA, scheduled report, advanced AI, branding, dan priority support.

## Enterprise

Custom branch/user, SSO, dedicated environment bila diperlukan, custom retention, enterprise SLA, audit export, custom integration, dan custom AI policy.

Pricing nominal belum ditetapkan dan tetap menjadi hypothesis sampai pilot dan cost model tersedia.

---

# 21. UI/UX INFORMATION ARCHITECTURE

Halaman MVP:

- Login dan authentication recovery.
- Tenant onboarding.
- Owner dashboard.
- Branch dashboard.
- Survey list, builder, preview, publish.
- Campaign dan invitation.
- Public survey.
- Feedback inbox dan detail.
- Recovery ticket list dan detail.
- Google connections dan locations.
- Review inbox dan detail.
- Draft and approval workspace.
- Knowledge base.
- Users, roles, branches, tenant settings.
- Subscription dan usage.
- Audit logs.
- Platform admin.
- AI operations basic view.

Setiap halaman harus memiliki permission-aware navigation dan tidak menampilkan menu yang tidak relevan, tanpa mengandalkan penyembunyian UI sebagai pengganti authorization server-side.

---

# 22. TECHNICAL BASELINE

## 22.1 Core stack

- Backend: Laravel 12.
- Database: PostgreSQL.
- Cache dan queue: Redis.
- Frontend: Blade, Tailwind CSS, Alpine.js, atau Inertia React setelah keputusan architecture step.
- Storage: S3-compatible.
- Authentication: Laravel Fortify atau Sanctum sesuai interface.
- Permission: Spatie Permission.
- Web server: Nginx.
- Runtime: PHP 8.3 atau production-supported version.
- Error tracking: Sentry atau setara.
- Observability: OpenTelemetry-compatible.

## 22.2 AI baseline

MVP dapat memanggil AI dari Laravel bila structured output, timeout, retry, audit, prompt versioning, guardrail, dan cost logging terpenuhi.

AI service terpisah direkomendasikan ketika orchestration, tool calling, model providers, scaling, dan tracing menjadi lebih kompleks.

Keputusan repository dan deployment topology final dilakukan pada Step 3.

---

# 23. TESTING REQUIREMENTS

## 23.1 Functional

Authentication, tenant, branch, RBAC, survey, campaign, response, metric, feedback, ticket, SLA, escalation, OAuth, location mapping, sync, draft, approval, publish, subscription, metering, export, dan audit.

## 23.2 Multi-tenant

- Tenant A tidak dapat membaca atau mengubah tenant B.
- Branch scope berlaku pada UI, API, export, search, dashboard, queue, storage, dan AI retrieval.
- Cache key tenant-scoped.
- Background job memulihkan tenant context dengan aman.

## 23.3 AI evaluation

Dataset harus mencakup Bahasa Indonesia informal, English, mixed language, typo, sarcasm, emoji, spam, threat, legal, fraud, medical complaint, PII, prompt injection, abusive, ambiguous, dan long review.

## 23.4 Security

Broken access control, privilege escalation, OAuth leakage, CSRF, XSS, SQL injection, IDOR, webhook forgery, file upload, rate-limit bypass, prompt injection, tool abuse, secret exposure, audit tampering, dan session fixation.

## 23.5 Performance

Public survey, review sync, dashboard, large tenant, multi-branch reporting, queue backlog, AI concurrency, bulk import/export, dan notification burst.

---

# 24. RELEASE GATES

## 24.1 MVP pilot gate

- Core workflow dapat berjalan end-to-end.
- Tenant isolation tests lulus.
- Permission dan audit tests lulus.
- Backup dan restore diuji.
- AI guardrail dan structured output diuji.
- Human approval aktif.
- Cost limit dan kill switch aktif.
- Pilot environment di-deploy.
- Runtime smoke test lulus.
- Monitoring dan alert dasar aktif.
- Tidak ada unresolved critical issue.

## 24.2 Production gate

Selain pilot gate:

- OAuth production readiness terverifikasi.
- Google policy dan API terbaru diverifikasi.
- Token refresh dan reauthorization diuji.
- Load dan failure testing lulus.
- Incident, rollback, backup, dan restore runbook tersedia.
- Data retention, export, dan deletion diuji.
- Security review selesai.
- Evidence lengkap.

GO tidak boleh diberikan berdasarkan mock, local-only test, atau klaim tanpa runtime evidence.

---

# 25. RISKS DAN MITIGASI

## 25.1 Google API approval dan policy change

**Risiko:** Approval tertunda atau endpoint/policy berubah.  
**Mitigasi:** Mulai readiness lebih awal, abstraction integration, feature flag, manual fallback, dan verifikasi ulang sebelum production.

## 25.2 AI output unsafe atau inaccurate

**Risiko:** PII leakage, hallucination, severity miss, atau respons tidak sesuai.  
**Mitigasi:** Structured output, guardrail, evaluation dataset, human approval, confidence threshold, redaction, dan kill switch.

## 25.3 Cross-tenant leakage

**Risiko:** Dampak kritis pada trust dan compliance.  
**Mitigasi:** Tenant-aware architecture, global scopes yang aman, explicit tests, cache/storage isolation, authorization server-side, dan security review.

## 25.4 Scope expansion

**Risiko:** MVP terlambat dan fondasi tidak stabil.  
**Mitigasi:** Non-goals, phased roadmap, change control, dan Master Source update.

## 25.5 Cost AI tidak terkendali

**Risiko:** Margin negatif.  
**Mitigasi:** Metering, token budget, cheaper model routing yang aman, caching bila sesuai, rate limit, tenant limits, dan cost dashboard.

## 25.6 Low survey response

**Risiko:** Nilai analytics dan AI rendah.  
**Mitigasi:** Timing experiment, mobile-first survey, short template, channel mix, reminder limit, dan consent-aware delivery.

## 25.7 Operational adoption

**Risiko:** Ticket dibuat tetapi tidak ditangani.  
**Mitigasi:** Clear assignment, SLA, action queue, notifications, training, pilot champion, dan usage review.

---

# 26. DEPENDENCIES

- Google Cloud project, OAuth consent, API access, dan production credential.
- Email provider dan domain authentication.
- WhatsApp Business Platform decision atau link-only pilot strategy.
- LLM provider account dan cost controls.
- Object storage, Redis, PostgreSQL, monitoring, backup target.
- Privacy Policy, Terms of Service, support contact, dan incident process.
- Pilot tenant data, consent, users, branches, baseline metrics, dan operational owner.

---

# 27. IMPLEMENTATION PHASES

1. Product discovery dan PRD.
2. Persona serta pilot use cases.
3. Repository dan architecture decision.
4. Domain dan branding.
5. Sprint roadmap.
6. Database schema.
7. Wireframe.
8. SaaS foundation.
9. Survey dan CSAT.
10. Feedback inbox.
11. Recovery ticket.
12. Basic AI.
13. Google integration.
14. AI review draft dan approval.
15. Agentic orchestration.
16. Analytics.
17. Billing dan platform admin.
18. Pilot.
19. Production readiness.

---

# 28. STEP 2 — PERSONA DAN PILOT USE CASE BASELINE

Step 2 telah menetapkan baseline berikut:

- Pilot tenant: Klinik Gigi Daengtisia.
- Recommended pilot branch: Daengtisia Pusat, subject to final operational and Google-access verification.
- Primary personas: Business Owner, Pilot Coordinator/Corporate Admin, Branch Manager, Recovery Assignee, dan Reputation Approver.
- Supporting personas: Customer/Guardian, Read-only Analyst/Auditor, Platform Support/AI Operations, dan DaengtisiaMS Integration.
- Doctors, nurses, cashiers, dan Admin Klinik tidak diwajibkan menjadi operator console pada pilot pertama.
- Preferred trigger: `VisitCompleted` from DaengtisiaMS.
- Target integration: authenticated and idempotent API/webhook, with controlled CSV/manual import and QR as truthful fallbacks.
- Primary invitation: unique WhatsApp link; QR mandatory fallback; email optional.
- Default invitation delay: 60 minutes, configurable within 30–120 minutes.
- Frequency cap: one invitation per customer per 14 calendar days; maximum one reminder after 24 hours; expiry seven days.
- Survey: CSAT, CES, NPS, optional comment, and conditional complaint/follow-up.
- All Google Review replies require human approval during the pilot.
- Review gating remains prohibited.
- Pilot data excludes diagnosis, clinical notes, medical-record number, prescription, odontogram, and other clinical records by default.
- Pilot operation target: eight weeks after readiness and setup.
- Hard gates prioritize tenant isolation, privacy, authorization, idempotency, truthful external states, and complete evidence.

Detailed personas, use cases, SLA, metrics, data boundaries, evidence, and GO/WATCH/NO-GO criteria are authoritative in:

`docs/product/PERSONA_AND_PILOT_USE_CASES_v1.0.0.md`

## 28.1 Remaining Decisions for Step 3 and Readiness

1. Repository application structure and bounded modules.
2. Blade/Alpine versus Inertia/React.
3. AI orchestration topology for MVP.
4. Environment and deployment topology.
5. Exact DaengtisiaMS event contract and authentication.
6. Exact named pilot users and final role combinations.
7. Final verification of Daengtisia Pusat as pilot branch.
8. Google Business Profile access readiness.
9. Approved privacy, recovery, compensation, and communication policies.
10. Provider decisions for WhatsApp, email, LLM, storage, monitoring, and backup.

# 29. ACCEPTANCE CRITERIA PRD STEP 1 DAN STEP 2

Step 1 dianggap selesai sebagai dokumentasi apabila:

- Nama produk dan positioning konsisten.
- Sasaran, non-goals, persona, workflow, scope MVP, dan functional requirements terdokumentasi.
- Multi-tenant, multi-branch, human approval, privacy, audit, dan review policy tercakup.
- AI architecture, guardrail, tracing, cost, dan manual fallback tercakup.
- Non-functional requirements, testing, risk, dependency, dan release gate tercakup.
- Open decisions untuk Step 2 dicatat tanpa membuat asumsi palsu.
- Master Source diperbarui dengan status Step 1.

**Status acceptance Step 1:** PASS.  
**Status acceptance Step 2:** PASS untuk baseline persona dan use case pilot.  
**Status implementasi aplikasi:** NOT STARTED.  
**Status deployment:** NOT STARTED.  
**Status pilot runtime:** NOT STARTED / NOT READY.  
**Status production:** NOT READY.

---

# 30. DEFINITION OF DONE PRODUK

Fitur Aish Agentic AI hanya dapat dinyatakan selesai apabila:

- Scope dan acceptance criteria terpenuhi.
- Code review, migration, permission, tenant isolation, tests, dan security checks lulus.
- UI states dan audit tersedia.
- Documentation dan Master Source diperbarui.
- CI lulus.
- Deployment serta runtime verification dilakukan bila diwajibkan.
- External integration diverifikasi dengan provider nyata.
- Evidence tersedia.
- Tidak ada critical issue.
- Status dilaporkan secara jujur.

---

# 31. CHANGELOG

## Version 1.1.0 — Persona and Pilot Use Case Baseline

**Tanggal:** 13 Juli 2026

- Menyelesaikan Step 2 dokumentasi.
- Menetapkan persona utama dan pendukung, Daengtisia Pusat sebagai recommended pilot branch, role coverage, invitation defaults, survey baseline, data boundary, recovery/SLA, Google Review approval, pilot metrics, dan evidence requirements.
- Menambahkan referensi kanonik ke `PERSONA_AND_PILOT_USE_CASES_v1.0.0.md`.
- Mempertahankan status implementasi aplikasi sebagai NOT STARTED.

## Version 1.0.0 — PRD Baseline

**Tanggal:** 13 Juli 2026

- Membuat baseline PRD berdasarkan Master Source Aish Agentic AI.
- Menetapkan sasaran MVP, non-goals, persona, jobs to be done, functional requirements, workflows, AI requirements, security, privacy, data, testing, release gates, risks, dependencies, dan open decisions.
- Menandai implementasi aplikasi sebagai PLANNED dan tidak mengklaim code, deployment, atau production readiness.

---

# END OF PRODUCT REQUIREMENT DOCUMENT

# AISH AGENTIC AI — PRD VERSION 1.1.0
