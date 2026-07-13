# AISH AGENTIC AI

# PERSONA DAN USE CASE PILOT

**Dokumen:** Persona dan Use Case Pilot  
**Versi:** 1.0.0  
**Status:** STEP 2 BASELINE COMPLETE — IMPLEMENTASI APLIKASI BELUM DIMULAI  
**Tanggal:** 13 Juli 2026  
**Timezone:** Asia/Makassar  
**Pemilik produk:** Aish Tech Solution  
**Repository kanonik:** `makemesick91-code/aish_agentic_ai`  
**Master Source kanonik:** Aish Agentic AI v2.2.0  
**PRD terkait:** Aish Agentic AI PRD v1.1.0  
**Pilot tenant:** Klinik Gigi Daengtisia  
**Cabang pilot yang direkomendasikan:** Daengtisia Pusat  

---

# 1. TUJUAN DOKUMEN

Dokumen ini menyelesaikan Step 2 roadmap kanonik Aish Agentic AI dengan menetapkan:

- Persona utama, pendukung, eksternal, stakeholder, dan sistem.
- Model operasional pilot pertama.
- Use case pilot berdasarkan prioritas P0, P1, dan P2.
- Workflow survei, invitation, customer recovery, Google Review, approval, dan analytics.
- Batas minimum data serta aturan privacy untuk pilot healthcare.
- Severity, SLA, escalation, contact policy, dan compensation authority.
- Target metrik, acceptance criteria, exit criteria, risiko, serta evidence pilot.

Dokumen ini tidak menyatakan bahwa code aplikasi, database, AI runtime, integrasi Google, deployment, atau pilot runtime telah tersedia.

---

# 2. RINGKASAN KEPUTUSAN PILOT

Baseline Step 2 adalah:

1. Pilot tenant pertama adalah **Klinik Gigi Daengtisia**.
2. Cabang pertama yang direkomendasikan adalah **Daengtisia Pusat** karena merupakan lokasi utama. Apabila kesiapan staf atau akses Google Business Profile belum terpenuhi, pemilik dapat menggantinya dengan satu cabang alternatif melalui decision log tanpa mengubah scope produk.
3. Pilot dimulai dari satu cabang dan satu Google location. Core architecture tetap wajib multi-tenant dan multi-branch.
4. Minimum role coverage adalah Business Owner, Pilot Coordinator, Branch Manager, Recovery Assignee, dan Reputation Approver. Satu orang boleh memegang beberapa role yang kompatibel.
5. Dokter, perawat, kasir, dan Admin Klinik menjadi stakeholder atau event source, tetapi tidak diwajibkan mengoperasikan console Aish Agentic AI pada pilot pertama.
6. Channel invitation utama adalah unique survey link melalui WhatsApp. QR wajib tersedia sebagai fallback. Email bersifat opsional.
7. Trigger yang dituju adalah event `VisitCompleted` dari DaengtisiaMS. Integrasi target menggunakan API/webhook terautentikasi; controlled CSV/manual import menjadi fallback sementara yang harus ditampilkan secara jujur.
8. Delay default adalah 60 menit setelah visit selesai dan dapat dikonfigurasi dalam rentang 30–120 menit.
9. Sending window default adalah 09.00–20.00 Asia/Makassar. Event di luar window dijadwalkan pada window berikutnya.
10. Frequency cap default adalah satu invitation per customer dalam 14 hari kalender, dengan maksimum satu reminder setelah 24 jam.
11. Invitation kedaluwarsa setelah tujuh hari.
12. Survei dibuat singkat: CSAT, CES, NPS, komentar opsional, dan pertanyaan conditional untuk komplain/follow-up.
13. Workflow manual wajib tetap berjalan apabila AI tidak tersedia.
14. Seluruh balasan Google Review wajib melalui human approval selama pilot.
15. Review gating dilarang. Akses Google Review tidak boleh bergantung pada skor atau sentimen survei.
16. Feedback critical/high-risk wajib ditangani manusia melalui kanal privat.
17. Diagnosis, clinical notes, prescription, medical record number, odontogram, dan detail perawatan tidak boleh dikirim ke AI provider atau dimasukkan ke balasan publik secara default.
18. Durasi operasi pilot adalah delapan minggu setelah readiness, didahului setup dan training.
19. Pilot dinilai berdasarkan reliability, safety, response, recovery operation, adoption, dan evidence—bukan dengan memaksa kenaikan public rating.
20. Step 2 hanya menyelesaikan dokumentasi. Implementasi aplikasi tetap **NOT STARTED**.

---

# 3. TUJUAN PILOT

Pilot harus membuktikan apakah Aish Agentic AI mampu secara aman dan konsisten:

- Mengumpulkan feedback setelah layanan selesai.
- Menghitung CSAT, NPS, dan CES.
- Menemukan feedback negatif, high-risk, atau berulang.
- Membuat, meng-assign, dan melacak customer recovery ticket.
- Membantu staf merespons lebih cepat tanpa menggantikan keputusan manusia.
- Menyinkronkan Google Review apabila authorized access tersedia.
- Membuat draft balasan aman dan mengarahkannya ke approval.
- Menampilkan action-oriented dashboard yang akurat untuk owner dan branch manager.
- Menjaga audit trail dan truthful external states.
- Menjalankan fungsi utama saat AI atau external provider bermasalah.

Pilot juga harus mengukur:

- Hambatan pengisian survei.
- Hambatan adopsi operasional.
- False positive dan false negative klasifikasi AI.
- Kelayakan SLA.
- Biaya per invitation, response, AI analysis, dan review draft.
- Gap integrasi antara DaengtisiaMS dan Aish Agentic AI.

---

# 4. MODEL PERSONA

## 4.1 Persona Eksternal — Customer atau Wali

**Peran:** Pasien/customer yang menerima layanan atau wali/contact sah ketika diperlukan.  
**Tujuan:** Memberi feedback dengan cepat dan aman serta meminta tindak lanjut tanpa mengulang informasi.  
**Pain point:** Form panjang, privacy tidak jelas, pesan berulang, respons defensif, dan tidak ada tindak lanjut.  
**Kebutuhan:** Survei mobile-first, consent yang jelas, opt-out, kanal follow-up privat, dan tidak perlu membuat akun.  
**Indikator sukses:** Survei selesai kurang dari dua menit dan tidak ada data medis yang dibuka ke publik.  
**Akses:** Tidak memiliki akses tenant console; hanya dapat membuka token survei yang valid, scoped, dan expiring.

## 4.2 Persona Utama — Business Owner / Executive Sponsor

**Tujuan:** Mengetahui customer experience, risiko cabang, komplain terbuka, reputasi Google, dan action yang dibutuhkan.  
**Keputusan utama:** Scope pilot, policy, escalation, compensation approval, privacy, dan GO/NO-GO.  
**Halaman utama:** Owner dashboard, executive summary, branch trend, critical ticket, SLA, Google rating, audit, dan pilot scorecard.  
**Indikator sukses:** Dapat menemukan masalah paling mendesak dan PIC-nya dalam waktu maksimal lima menit.  
**Data scope:** Seluruh tenant.  
**Batasan:** Tidak dapat melewati audit, privacy, permission, dan public-reply approval.

## 4.3 Persona Utama — Pilot Coordinator / Corporate Admin

**Tujuan:** Menyiapkan tenant dan menjaga pilot tetap berjalan.  
**Tanggung jawab:** User, branch, survey, campaign, invitation rules, knowledge base, consent text, integration state, dan weekly evidence.  
**Halaman utama:** Tenant settings, branch, users, survey builder, campaign, integration health, notification rules, dan audit.  
**Indikator sukses:** Konfigurasi lengkap, dapat direproduksi, dan tidak membutuhkan direct production-data edit.  
**Batasan:** Tidak boleh memberi excessive permission atau mempublikasikan balasan tanpa role yang benar.

## 4.4 Persona Utama — Branch Manager

**Tujuan:** Melihat feedback cabang, menetapkan PIC, memenuhi SLA, dan mengurangi root cause berulang.  
**Tanggung jawab:** Triage, assignment, escalation, resolution review, dan branch action review.  
**Halaman utama:** Branch dashboard, action queue, feedback detail, ticket detail, SLA, dan root-cause report.  
**Indikator sukses:** Seluruh critical/high feedback diakui dan di-assign sesuai SLA.  
**Data scope:** Cabang yang menjadi scope-nya.

## 4.5 Persona Utama — Recovery Assignee / Customer Service

**Tujuan:** Menghubungi customer secara privat, mendokumentasikan outcome, dan menyelesaikan recovery action.  
**Tanggung jawab:** Membaca ticket, menggunakan approved contact draft, mencatat contact attempt, menambah internal note, mengusulkan corrective action, dan meminta escalation.  
**Halaman utama:** My tasks, ticket detail, contact history, approved templates, dan resolution checklist.  
**Indikator sukses:** Customer mendapat follow-up yang sopan dan evidence ticket lengkap.  
**Batasan:** Tidak boleh menjanjikan refund, discount, compensation, legal admission, atau public reply tanpa kewenangan.

## 4.6 Persona Utama — Reputation Approver

**Tujuan:** Mempublikasikan balasan Google Review yang aman, konsisten, dan tepat waktu.  
**Tanggung jawab:** Menilai AI draft, memeriksa privacy, mengedit, approve/reject, publish, dan memantau publication state.  
**Halaman utama:** Review inbox, review detail, draft comparison, guardrail result, approval queue, dan publication log.  
**Indikator sukses:** Balasan profesional, tidak mengandung private/medical data, dan status eksternalnya terverifikasi.  
**Batasan:** Tidak boleh melakukan review gating, meminta rating tertentu, membalas dengan agresif, atau menggunakan hidden auto-publish.

## 4.7 Persona Pendukung — Read-only Analyst / Auditor

**Tujuan:** Memvalidasi tren, akurasi data, dan efektivitas kontrol tanpa mengubah data operasional.  
**Halaman utama:** Analytics, audit logs, pilot scorecard, data reconciliation, dan evidence export.  
**Indikator sukses:** Dapat mereproduksi KPI dan menelusuri material action.  
**Batasan:** Read-only; export harus permissioned dan audited.

## 4.8 Persona Pendukung — Platform Support / AI Operations

**Tujuan:** Memantau tenant health, integration failure, queue/agent failure, cost, dan guardrail event.  
**Halaman utama:** Platform admin, failed agent runs, integration health, cost, trace, incident log, dan support notes.  
**Indikator sukses:** Failure dapat didiagnosis tanpa cross-tenant exposure dan tanpa silent mutation.  
**Batasan:** Support access diaudit; tidak memiliki hak public publishing.

## 4.9 Stakeholder — Dokter, Perawat, Kasir, dan Admin Klinik

Mereka dapat memberi context atau menghasilkan completed-service event, tetapi tidak wajib memakai console Aish Agentic AI pada pilot pertama. Pilot tidak boleh memperlambat pelayanan klinik atau menambah duplikasi dokumentasi medis.

## 4.10 System Persona — Integrasi DaengtisiaMS

**Peran:** Penghasil event layanan selesai setelah server-side authentication.  
**Tujuan:** Mengirim minimum eligible `VisitCompleted` event dan menerima durable acknowledgement.  
**Batasan:** Event pilot tidak membawa diagnosis, clinical note, odontogram, prescription, atau dokumen medis.

---

# 5. MINIMUM ROLE COVERAGE PILOT

| Coverage | Minimum | Dapat digabung dengan |
|---|---:|---|
| Business Owner / Executive Sponsor | 1 | Reputation Approver |
| Pilot Coordinator / Corporate Admin | 1 | Integration Admin |
| Branch Manager | 1 | Recovery Approver |
| Recovery Assignee | 1–2 | Customer Service |
| Reputation Approver | 1 | Business Owner |
| Read-only Analyst/Auditor | 0–1 | Independent reviewer |

Pilot dapat berjalan dengan lima orang bernama apabila role yang kompatibel digabungkan. Kombinasi tidak boleh menghilangkan meaningful approval pada tindakan berisiko tinggi.

---

# 6. DESAIN SURVEI PILOT

## 6.1 Pertanyaan Utama

1. **CSAT:** “Seberapa puas Anda dengan pengalaman layanan hari ini?” — skala 1–5.
2. **CES:** “Seberapa mudah proses layanan Anda hari ini?” — skala 1–5.
3. **NPS:** “Seberapa besar kemungkinan Anda merekomendasikan layanan kami kepada keluarga atau teman?” — skala 0–10.
4. **Komentar:** “Apa yang paling berkesan atau perlu kami perbaiki?” — optional free text.

## 6.2 Pertanyaan Conditional

Jika CSAT 1–3 atau customer memilih membutuhkan follow-up:

- Kategori masalah: waktu tunggu, keramahan staf, komunikasi, kebersihan, kenyamanan, pembayaran, appointment, fasilitas, service quality, privacy, safety, atau lainnya.
- Consent follow-up: “Bolehkah tim kami menghubungi Anda secara privat untuk menindaklanjuti masukan ini?”
- Preferred channel: WhatsApp atau telepon jika diizinkan.

## 6.3 Aturan Survei

- Mobile-first dan tidak memerlukan login.
- Target completion time kurang dari dua menit.
- Satu response per invitation, kecuali tersedia controlled correction flow.
- Token sulit ditebak, scoped, dan expiring.
- Tidak meminta diagnosis atau riwayat medis.
- Tidak meminta rating lima bintang.
- Google Review link, jika ditampilkan, bersifat netral dan tersedia sama untuk seluruh respondent yang eligible.
- Consent dan privacy notice terlihat sebelum submit.

---

# 7. INVITATION DAN ELIGIBILITY POLICY

## 7.1 Eligible Event

Invitation dapat dibuat apabila:

- Status visit/service adalah `completed`.
- Event berasal dari tenant dan pilot branch yang benar.
- Record bukan cancelled, test, duplicate, atau data staf internal.
- Tersedia lawful communication path atau on-site QR.
- Untuk customer anak, komunikasi diarahkan ke guardian/contact yang dikonfigurasi.

## 7.2 Default Pilot

| Setting | Default |
|---|---|
| Trigger | `VisitCompleted` |
| Delay | 60 menit; configurable 30–120 menit |
| Sending window | 09.00–20.00 Asia/Makassar |
| Primary channel | Unique link melalui WhatsApp |
| Fallback | QR code / public campaign link dengan attribution aman |
| Optional channel | Email |
| Frequency cap | 1 invitation/customer/14 hari kalender |
| Reminder | Maksimum 1, setelah 24 jam |
| Expiration | 7 hari |
| Opt-out | Wajib |

## 7.3 Mode Integrasi

1. **Target:** Signed dan authenticated API/webhook dari DaengtisiaMS.
2. **Controlled fallback:** CSV/manual import yang hanya berisi minimum pilot fields.
3. **On-site fallback:** QR survey yang mencatat branch/campaign tanpa membuka customer identifier.

Fallback harus terlihat di analytics dan audit. Sistem tidak boleh menampilkan manual import sebagai real-time integration success.

---

# 8. BATAS MINIMUM DATA

## 8.1 Data yang Diizinkan

- `tenant_id` dan `branch_id`.
- External customer reference atau pseudonymous customer ID.
- Preferred display name jika dibutuhkan.
- Phone/email terenkripsi jika diperlukan untuk invitation atau follow-up.
- Consent dan opt-out state.
- Service event ID dan completion timestamp.
- Generic service category code jika dibutuhkan secara operasional.
- Survey invitation, response, answer, dan delivery state.
- Feedback analysis, ticket, SLA, action, dan audit.
- Google account/location/review data dari authorized API.

## 8.2 Data yang Dilarang secara Default

- Diagnosis.
- Clinical notes.
- Nomor rekam medis.
- Prescription atau medication details.
- Odontogram.
- Foto/scan klinis.
- Treatment-plan narrative.
- Insurance details.
- Payment-card atau bank-account data.
- Unredacted internal incident notes di AI prompt.

Pengecualian membutuhkan privacy/security review, lawful basis, data minimization, serta Master Source update.

---

# 9. USE CASE PILOT BERDASARKAN PRIORITAS

## 9.1 P0 — Wajib untuk Operasi Pilot

### UC-P0-01 — Onboarding Tenant dan Pilot Branch

**Actor:** Pilot Coordinator.  
**Outcome:** Tenant, Daengtisia Pusat, timezone, role, dan pilot settings dikonfigurasi.  
**Acceptance:** Tenant/branch scope lulus, setup diaudit, dan tidak ada cross-tenant visibility.

### UC-P0-02 — Membuat dan Mempublikasikan Survei

**Actor:** Pilot Coordinator.  
**Outcome:** Versioned survey CSAT, CES, NPS, comment, dan conditional follow-up dipublikasikan.  
**Acceptance:** Draft/published version immutable dan traceable; preview sesuai public page.

### UC-P0-03 — Menerima Completed-Service Event

**Actor:** Integrasi DaengtisiaMS.  
**Outcome:** Eligible `VisitCompleted` diterima tepat satu kali.  
**Acceptance:** Authentication, idempotency, tenant/branch mapping, validation, audit, dan prohibited-field handling lulus.

### UC-P0-04 — Membuat dan Mengirim Invitation

**Actor:** Campaign engine atau staff fallback.  
**Outcome:** Unique invitation dijadwalkan dan dikirim/dibagikan.  
**Acceptance:** Frequency cap, sending window, expiry, channel state, failure reason, dan truthful delivery state diterapkan.

### UC-P0-05 — Customer Mengisi Feedback

**Actor:** Customer atau wali.  
**Outcome:** Response tersimpan dan score dihitung.  
**Acceptance:** Token scope/expiry lulus, duplicate submission dikontrol, consent tersimpan, dan mobile usability baik.

### UC-P0-06 — Menganalisis Feedback dengan Manual Fallback

**Actor:** AI analysis service dan CX user.  
**Outcome:** Sentiment, topic, severity, risk, summary, confidence, dan suggested action tersedia.  
**Acceptance:** Structured output valid, model/prompt/cost tercatat, guardrail berjalan, dan manual classification tersedia saat AI gagal.

### UC-P0-07 — Membuat Recovery Ticket Otomatis

**Actor:** Rule engine.  
**Outcome:** Negative/high-risk feedback menghasilkan branch-scoped ticket.  
**Acceptance:** Creation idempotent; severity, priority, SLA, reason code, dan audit tersedia.

### UC-P0-08 — Triage, Assignment, dan Escalation

**Actor:** Branch Manager.  
**Outcome:** Ticket memiliki accountable assignee dan escalation path.  
**Acceptance:** Branch permission, SLA clock, notification deduplication, dan reassignment history lulus.

### UC-P0-09 — Menghubungi Customer secara Privat

**Actor:** Recovery Assignee.  
**Outcome:** Staf menggunakan approved draft dan mencatat contact attempt serta response.  
**Acceptance:** Tidak ada public disclosure, consent/contact policy diterapkan, dan unauthorized compensation promise diblokir.

### UC-P0-10 — Resolution dan Closure Ticket

**Actor:** Recovery Assignee dan authorized approver.  
**Outcome:** Root cause, corrective action, outcome, dan evidence tercatat.  
**Acceptance:** Required fields, approval, SLA result, reopen path, dan audit lengkap.

### UC-P0-11 — Menghubungkan Google Business Profile

**Actor:** Integration Admin atau authorized owner.  
**Outcome:** Authorized account dan pilot location dipetakan ke branch.  
**Acceptance:** OAuth state validation, encrypted token, permission diagnostic, reauthorization state, dan disconnect flow lulus.

### UC-P0-12 — Sinkronisasi Google Review

**Actor:** Google integration worker.  
**Outcome:** Review dan existing reply tersinkron secara idempotent.  
**Acceptance:** Cursor, rate-limit handling, retry, sync log, external ID, dan truthful failure state lulus.

### UC-P0-13 — Draft, Approval, dan Publish Reply

**Actor:** AI Response Assistant dan Reputation Approver.  
**Outcome:** Safe draft ditinjau, diedit, di-approve, dan dipublikasikan.  
**Acceptance:** Human approval wajib, PII/medical guardrail lulus, external API response tersimpan, dan `Published` hanya setelah verification.

### UC-P0-14 — Owner dan Branch Dashboard

**Actor:** Owner dan Branch Manager.  
**Outcome:** Feedback, SLA, recovery, dan review metric terlihat.  
**Acceptance:** KPI definition terdokumentasi, branch scope diterapkan, dan dashboard reconciled dengan source records.

### UC-P0-15 — Audit dan Export Evidence

**Actor:** Auditor atau Pilot Coordinator.  
**Outcome:** Material action dan pilot KPI dapat diekspor.  
**Acceptance:** Export permissioned, tenant-scoped, timestamped, dan audited.

### UC-P0-16 — Operasi saat AI atau Provider Gagal

**Actor:** Operations staff.  
**Outcome:** Survey, manual triage, ticketing, approval, dan audit tetap dapat digunakan.  
**Acceptance:** Failure state jujur, retry tidak membuat duplicate action, dan kill switch/manual fallback berfungsi.

## 9.2 P1 — Bernilai saat Stabilisasi

- Satu reminder dengan opt-out dan delivery audit.
- Weekly owner digest.
- Approved knowledge-base templates dan branch information.
- Root-cause trend dan repeated-complaint detection.
- Saved filters dan assignment queue.
- Pilot scorecard dan cost-per-run report.
- Safe bulk assignment tanpa bulk auto-publish.

## 9.3 P2 — Ditunda sampai Evidence Mendukung

- Fully automated WhatsApp Business Platform delivery.
- Multi-branch pilot expansion.
- Controlled low-risk auto-publish.
- Advanced predictive analytics.
- Social media inbox selain Google.
- Voice/call agent.
- Automated refund, discount, atau compensation.

---

# 10. CUSTOMER RECOVERY POLICY

## 10.1 Severity dan SLA Baseline

| Severity | Contoh | Human handling | Initial acknowledgement | Private first contact/action | Target resolution |
|---|---|---|---:|---:|---:|
| Critical | Safety/medical danger, privacy leak, legal threat, fraud, violence, discrimination | Immediate owner/manager escalation; public reply ditahan | 15 menit | 60 menit selama operational coverage | Case-specific dengan incident owner |
| High | CSAT 1–2, severe service failure, repeated unresolved complaint | Mandatory human review dan branch assignment | 30 menit | 2 jam | Action plan dalam 8 jam |
| Medium | CSAT 3, mixed/moderate complaint | Human triage | 4 jam | 1 hari kerja | 2 hari kerja |
| Low | Suggestion atau minor issue | Queue review | 1 hari kerja | Jika follow-up bermanfaat/consented | 5 hari kerja |

Critical alert tidak menunggu digest atau quiet hours. Perubahan SLA hanya boleh dilakukan melalui recorded decision setelah evidence review.

## 10.2 Contact Policy

- Customer-specific matter ditangani melalui kanal privat.
- Maksimum dua proactive contact attempts dalam tiga hari kerja kecuali customer meminta komunikasi dilanjutkan.
- Customer yang opt-out tidak boleh dihubungi berulang.
- Tidak boleh meminta customer menghapus atau mengubah review sebagai syarat recovery.
- Compensation tidak boleh dikaitkan dengan positive review.

## 10.3 Definisi Recovered Customer

Customer dihitung recovered apabila:

1. Masalah memiliki owner dan root cause/action terdokumentasi.
2. Contact yang sesuai consent atau alternative service action sudah dilakukan.
3. Customer mengonfirmasi resolution atau menerima next action; jika tidak merespons, manager hanya dapat menutup setelah contact-attempt policy selesai dengan evidence.
4. Tidak ada critical safety, privacy, legal, atau clinical-risk item yang belum selesai.
5. Required approval lengkap.
6. Recovery tidak boleh disimpulkan hanya karena ticket closed atau review berubah.

---

# 11. COMPENSATION AUTHORITY

| Role | Kewenangan pada pilot pertama |
|---|---|
| AI agent | Hanya dapat menyarankan; tidak dapat approve, promise, atau execute compensation |
| Recovery Assignee | Dapat meminta maaf dan mengusulkan non-financial next step; tidak dapat membuat financial commitment |
| Branch Manager | Dapat merekomendasikan refund/discount/compensation; approval tetap wajib |
| Business Owner / designated approver | Dapat approve sesuai written tenant policy |
| Platform Support | Tidak memiliki kewenangan compensation tenant |

Baseline ini tidak membuat batas nominal fiktif. Amount dan remedy yang diperbolehkan harus berasal dari approved Daengtisia policy.

---

# 12. ATURAN GOOGLE REVIEW PILOT

- Hanya account/location yang dikontrol authorized Daengtisia representative yang dapat dihubungkan.
- Pilot dimulai dari satu mapped Google location.
- Human approval wajib untuk seluruh balasan.
- Review content dianggap untrusted input dan tidak boleh menentukan system/tool behavior.
- Balasan publik tidak boleh menyebut diagnosis, procedure, visit history, doctor-patient relation, detail payment dispute, atau private fact lainnya.
- Review gating, incentive, requested star rating, fake review, dan selective access dilarang.
- Neutral Google Review link, jika ditampilkan, harus konsisten untuk seluruh eligible respondent tanpa melihat score.
- Failed API call harus tetap berstatus `Publication failed` atau status setara.
- Apabila OAuth/API belum siap, pilot CSAT/recovery dapat berjalan dengan scope Google berstatus BLOCKED; mock tidak boleh diklaim sebagai integration success.

## 12.1 Brand Voice Pilot

- Hangat dan sopan.
- Profesional dan ringkas.
- Empatik tanpa legal admission.
- Tidak defensif.
- Bahasa Indonesia sebagai default dan mengikuti bahasa reviewer jika aman.
- Kasus sensitif diarahkan ke official private channel.

## 12.2 Pola Template Aman

**Positif:** Berterima kasih kepada reviewer, menghargai waktu yang diberikan, dan menegaskan komitmen layanan tanpa membuka detail kunjungan.

**Negatif/sensitif:** Berterima kasih atas masukan, menyatakan tim ingin memahami kasus melalui kanal resmi privat, dan tidak mengonfirmasi fakta sensitif di ruang publik.

---

# 13. FASE OPERASI PILOT

## 13.1 Persiapan — maksimal 2 minggu

- Menetapkan branch champion dan named users.
- Memverifikasi Google account/location ownership.
- Menyetujui privacy notice, consent text, contact policy, dan brand voice.
- Menyiapkan survey, severity mapping, SLA, knowledge base, dan training.
- Memvalidasi minimum data mapping dan test records.
- Menetapkan baseline data atau metode baseline capture.

## 13.2 Minggu 1–2 — Controlled Baseline dan Shadow Assistance

- Mengirim invitation pada limited cohort.
- Membandingkan AI classification dengan human classification.
- Menggunakan manual approval untuk seluruh tindakan.
- Melakukan daily reconciliation invitation, response, ticket, review, dan dashboard.
- Menyelesaikan critical workflow/configuration issue sebelum menaikkan volume.

## 13.3 Minggu 3–4 — Assisted Live Operation

- Memperluas ke planned eligible cohort.
- Mengaktifkan SLA dan escalation monitoring.
- Menggunakan AI draft dengan human review.
- Menilai false positive/negative setiap minggu.

## 13.4 Minggu 5–8 — Stabilisasi dan Evaluasi

- Menjalankan intended pilot volume.
- Mengukur cost, response, recovery, review operation, adoption, dan failure.
- Menjalankan security/privacy serta data reconciliation checks.
- Membuat final GO/WATCH/NO-GO report untuk ekspansi.

---

# 14. METRIK PILOT

## 14.1 Hard Safety dan Correctness Gates

- Nol confirmed cross-tenant data exposure.
- Nol unauthorized public reply.
- Nol known PII/medical leakage pada public reply.
- 100% public reply memiliki recorded human approval.
- 100% critical incident memiliki owner, timeline, dan audit evidence.
- Tidak ada external action yang disebut sukses sebelum provider verification.
- Tidak ada duplicated invitation, ticket, atau reply akibat retry/idempotency failure.

Critical failure pada gate ini menghasilkan NO-GO sampai diperbaiki dan diuji ulang.

## 14.2 Operational Targets

Target berikut merupakan pilot hypothesis, bukan jaminan:

| Metric | Initial target |
|---|---:|
| Eligible invitation creation success | ≥ 90% |
| Delivery success untuk sendable invitation | ≥ 85% |
| Survey response rate | ≥ 20% |
| Completion rate setelah survey dibuka | ≥ 80% |
| Negative feedback yang di-triage | ≥ 95% |
| Critical/high first response within SLA | ≥ 90% |
| Google Review dengan reply/approved disposition dalam 48 jam | ≥ 90% |
| Median Google reply time | < 24 jam |
| Structured AI output validity | ≥ 99% |
| Critical/high severity recall pada evaluation set | ≥ 95% |
| Weekly active operational users | ≥ 80% dari named operators |
| Dashboard/source reconciliation | 100% untuk release-critical KPI |

Human edit rate, cost per AI run, recovery rate, repeat complaint rate, dan rating trend diukur tanpa memaksakan favorable threshold pada pilot pertama.

---

# 15. EVIDENCE REQUIREMENTS

Evidence pilot minimum:

- Named role coverage dan training completion.
- Survey version serta consent/privacy text.
- Eligibility, frequency cap, dan invitation logs.
- Data mapping dan prohibited-field test.
- AI evaluation dataset dan hasil.
- Ticket/SLA serta escalation samples.
- Recovery outcome dan contact-attempt evidence.
- OAuth/location mapping dan sync evidence saat Google aktif.
- Human approval dan publication-state evidence.
- Audit serta permission tests.
- Data reconciliation report.
- Incident dan failure log.
- Cost dan usage report.
- Weekly checkpoint serta final pilot report.

Evidence harus tenant-safe dan tidak boleh menyimpan real customer PII di repository.

---

# 16. GO / WATCH / NO-GO PILOT

## GO untuk Ekspansi Terbatas

- Seluruh hard safety/correctness gates lulus.
- Tidak ada unresolved critical incident.
- Workflow dapat digunakan oleh named pilot roles.
- Operational target utama tercapai secara substansial atau memiliki approved remediation plan.
- AI/external failure path jujur dan dapat dipulihkan.
- Cost terukur dan dapat diterima.
- Owner menyetujui next rollout scope.

## WATCH

- Tidak ada critical safety breach, tetapi adoption, response rate, cost, AI quality, atau integration target masih membutuhkan perbaikan.
- Ekspansi dibatasi sampai corrective action terverifikasi.

## NO-GO

- Cross-tenant exposure.
- Unauthorized publishing.
- PII/medical leakage.
- Falsified success state.
- Uncontrolled duplicate action.
- Critical permission failure.
- Unresolved critical incident.
- Missing evidence untuk release-critical claim.

---

# 17. RISIKO DAN MITIGASI

| Risiko | Mitigasi |
|---|---|
| Pilot membebani staf klinik | Dokter/non-CX staff tidak diwajibkan memakai console; gunakan event integration dan concise action queue |
| Response rate rendah | Short mobile survey, timing test, QR fallback, maksimum satu reminder |
| AI melewatkan serious complaint | Human review, severity evaluation set, keyword/rule safety net, low-confidence escalation |
| Privacy bocor di public reply | Mandatory approval, redaction, guardrail, safe template, public-reply restriction |
| Akses Google belum tersedia | Mulai dari CSAT/recovery; tandai Google BLOCKED; jangan memalsukan integration |
| Duplicate send/action | Idempotency key, frequency cap, provider reconciliation, retry test |
| Staf menjanjikan compensation | Authority matrix, templates, permission, dan approval |
| KPI disalahartikan | KPI dictionary dan source reconciliation |
| Produk menjadi terlalu clinic-specific | Generic domain model; clinic mapping tetap di integration/configuration boundary |

---

# 18. OUT OF SCOPE PILOT PERTAMA

- Auto-publish Google reply.
- Automated refund, discount, atau compensation.
- Medical diagnosis atau clinical advice.
- Mengirim full medical record ke AI.
- Voice/call agent.
- Seluruh social media channel.
- Multi-branch rollout sebelum evidence cabang pertama.
- Advanced churn prediction.
- Full marketing automation.
- Native mobile app.
- Menggantikan workflow klinis DaengtisiaMS.

---

# 19. ACCEPTANCE CRITERIA DOKUMENTASI STEP 2

Step 2 selesai apabila:

- Persona utama, pendukung, eksternal, stakeholder, dan sistem terdokumentasi.
- Pilot branch, role coverage, channel, trigger, timing, survey, data boundary, SLA, serta approval rules eksplisit.
- P0/P1/P2 use cases memiliki actor, outcome, dan acceptance expectation.
- Review-gating prohibition dan human approval dipertahankan.
- Pilot metrics membedakan hard gate dan hypothesis.
- Evidence, GO/WATCH/NO-GO, risk, serta out-of-scope jelas.
- PRD dan Master Source konsisten.
- Tidak ada klaim implementasi aplikasi.

**Status dokumentasi Step 2:** COMPLETE.  
**Status implementasi aplikasi:** NOT STARTED.  
**Status runtime pilot:** NOT STARTED / NOT READY.  

---

# 20. OPEN DECISIONS UNTUK STEP 3 DAN PILOT READINESS

1. Repository application structure dan bounded modules.
2. Pilihan Blade/Alpine atau Inertia/React.
3. Single service atau separated AI orchestrator untuk MVP.
4. Infrastructure topology dan environment strategy.
5. DaengtisiaMS webhook/API contract serta authentication method.
6. Named pilot users dan final role combination.
7. Final confirmation bahwa Daengtisia Pusat menjadi pilot branch.
8. Google Business Profile ownership/access readiness.
9. Approved privacy notice, recovery policy, dan compensation policy Daengtisia.
10. Monetary authority thresholds jika ada.
11. Baseline volume dan Google metrics saat ini.
12. Provider decisions untuk email, WhatsApp, LLM, storage, monitoring, dan backup.

Open item tersebut tidak membatalkan completion dokumentasi Step 2. Item wajib ditutup sebelum gate implementasi atau pilot yang relevan.

---

# 21. CHANGELOG

## Version 1.0.0 — Step 2 Baseline

**Tanggal:** 13 Juli 2026

- Menetapkan persona dan minimum role coverage pilot.
- Menetapkan Daengtisia Pusat sebagai recommended pilot branch.
- Menetapkan invitation, survey, data minimization, recovery, SLA, Google Review, approval, metrics, serta evidence baseline.
- Menetapkan P0/P1/P2 use cases.
- Mempertahankan status implementasi aplikasi dan pilot runtime sebagai NOT STARTED.

---

# END OF PERSONA DAN USE CASE PILOT

# AISH AGENTIC AI — PERSONA DAN USE CASE PILOT VERSION 1.0.0
