# SIMS Presentation Dataset

All records in this document are fictional and are created only by the local/testing-only `PresentationDatabaseSeeder`.

## Login accounts

### Administrator

| Name | Email | Password | Account status |
|---|---|---|---|
| SIMS Presentation Administrator | `admin.presentation@sims.test` | `Admin@SIMS2026!` | Active |

### Staff / Registrar

All Staff accounts use the password `Staff@SIMS2026!`.

| Name | Email | Account status |
|---|---|---|
| Maria Elena Reyes | `registrar.presentation@sims.test` | Active, primary login |
| Roberto Miguel Santos | `roberto.santos.staff@sims.test` | Active |
| Catherine Anne Cruz | `catherine.cruz.staff@sims.test` | Active |
| Joseph Daniel Lim | `joseph.lim.staff@sims.test` | Active |
| Michelle Grace Dela Vega | `michelle.delavega.staff@sims.test` | Active |
| Ramon Paulo Mendoza | `ramon.mendoza.staff@sims.test` | Active |
| Patricia Louise Tan | `patricia.tan.staff@sims.test` | Active |
| Mark Anthony Flores | `mark.flores.staff@sims.test` | Active |
| Angela Marie Navarro | `angela.navarro.staff@sims.test` | Active |
| Victor Emmanuel Garcia | `victor.garcia.staff@sims.test` | Inactive |

### Students

All Student accounts use the password `Student@SIMS2026!`. Inactive accounts cannot sign in, but their records remain available for Admin/Staff review and reports.

| No. | Student name | Email | Program | Year | Section | Student status | Portal |
|---:|---|---|---|---:|---|---|---|
| 1 | Alyssa Mae Santos | `alyssa.santos.presentation@sims.test` | BSIT | 3 | 3A | Active | Active, primary login |
| 2 | Joshua Miguel Reyes | `joshua.reyes.presentation@sims.test` | BSIT | 2 | 2A | Active | Active |
| 3 | Nicole Anne Dela Cruz | `nicole.delacruz.presentation@sims.test` | BSCS | 1 | 1A | Active | Active |
| 4 | Gabriel Luis Tan | `gabriel.tan.presentation@sims.test` | BSCpE | 2 | 2A | Active | Active |
| 5 | Bianca Rose Mendoza | `bianca.mendoza.presentation@sims.test` | BSCE | 1 | 1B | Active | Active |
| 6 | Marco Paolo Garcia | `marco.garcia.presentation@sims.test` | BSEE | 4 | 4A | Active | Active |
| 7 | Sofia Isabelle Flores | `sofia.flores.presentation@sims.test` | BSECE | 3 | 3B | Active | Active |
| 8 | Daniel Miguel Cruz | `daniel.cruz.presentation@sims.test` | BSIE | 2 | 2B | Active | Active |
| 9 | Andrea Nicole Villanueva | `andrea.villanueva.presentation@sims.test` | BSIndTech | 1 | 1A | Active | Active |
| 10 | Nathaniel James Ramos | `nathaniel.ramos.presentation@sims.test` | BSABE | 4 | 4B | Active | Active |
| 11 | Carlo Miguel Bautista | `carlo.bautista.presentation@sims.test` | BSArch | 3 | 3A | Active | Active |
| 12 | Patricia Anne Lim | `patricia.lim.presentation@sims.test` | BSIT | 4 | 4B | Active | Active |
| 13 | John Patrick Navarro | `john.navarro.presentation@sims.test` | BSCS | 2 | 2B | Active | Active |
| 14 | Camille Grace Torres | `camille.torres.presentation@sims.test` | BSCpE | 3 | 3A | Active | Active |
| 15 | Ethan Gabriel Rivera | `ethan.rivera.presentation@sims.test` | BSCE | 2 | 2A | Active | Active |
| 16 | Isabella Marie Chua | `isabella.chua.presentation@sims.test` | BSEE | 1 | 1A | Active | Active |
| 17 | Luis Fernando Castillo | `luis.castillo.presentation@sims.test` | BSECE | 4 | 4B | Active | Active |
| 18 | Mikaela Joy Fernandez | `mikaela.fernandez.presentation@sims.test` | BSIE | 3 | 3B | Active | Active |
| 19 | Adrian Paolo Mercado | `adrian.mercado.presentation@sims.test` | BSIndTech | 2 | 2A | Active | Active |
| 20 | Denise Claire Aquino | `denise.aquino.presentation@sims.test` | BSABE | 1 | 1B | Active | Active |
| 21 | Francis Matthew Ong | `francis.ong.presentation@sims.test` | BSArch | 2 | 2B | Active | Active |
| 22 | Beatrice Anne Co | `beatrice.co.presentation@sims.test` | BSIT | 1 | 1A | Inactive | Inactive |
| 23 | Sean Vincent Flores | `sean.flores.presentation@sims.test` | BSCS | 4 | 4B | Graduated | Active |
| 24 | Katrina Louise Ramos | `katrina.ramos.presentation@sims.test` | BSCpE | 1 | 1A | Inactive | Inactive |
| 25 | Miguel Andres Soriano | `miguel.soriano.presentation@sims.test` | BSCE | 3 | 3B | Graduated | Active |
| 26 | Angela Mae Santiago | `angela.santiago.presentation@sims.test` | BSEE | 2 | 2B | Active | Active |
| 27 | Rafael Joseph Bautista | `rafael.bautista.presentation@sims.test` | BSECE | 1 | 1A | Active | Active |
| 28 | Jasmine Nicole Lim | `jasmine.lim.presentation@sims.test` | BSIE | 4 | 4A | Active | Active |
| 29 | Paulo Miguel Herrera | `paulo.herrera.presentation@sims.test` | BSIT | 2 | 2B | Active | Active |
| 30 | Chloe Patricia Garcia | `chloe.garcia.presentation@sims.test` | BSCS | 3 | 3A | Active | Active |

Student numbers are assigned sequentially as `2026-CEIT-0001` through `2026-CEIT-0030`.

## Academic structure

The existing CEIT program seeder provides these 10 programs:

| Code | Program |
|---|---|
| BSABE | Bachelor of Science in Agricultural and Biosystems Engineering |
| BSArch | Bachelor of Science in Architecture |
| BSCE | Bachelor of Science in Civil Engineering |
| BSCpE | Bachelor of Science in Computer Engineering |
| BSCS | Bachelor of Science in Computer Science |
| BSEE | Bachelor of Science in Electrical Engineering |
| BSECE | Bachelor of Science in Electronics and Communication Engineering |
| BSIE | Bachelor of Science in Industrial Engineering |
| BSIndTech | Bachelor of Science in Industrial Technology |
| BSIT | Bachelor of Science in Information Technology |

Six Year Level records are seeded. First Year through Fifth Year are active. Sixth Year is retained as an inactive example.

For each of the 10 programs, active sections `1A`, `1B`, `2A`, `2B`, through `5A`, `5B` are created. Two additional examples demonstrate inactive section behavior: BSIT Third Year `3C` is directly inactive, and BSIT Sixth Year `6A` belongs to the inactive Sixth Year Level. Total: 102 Section records.

## Enrollment and Student Portal examples

Each currently active student has three enrollment records:
- 2025-2026, First Semester
- 2025-2026, Second Semester
- 2026-2027, First Semester

Inactive and graduated student profiles have historical enrollment records for 2024-2025 and 2025-2026. Total: 86 enrollment records.

The first 16 students have Visitor Time-Out history. Alyssa Mae Santos has three sample records dated November 18, November 20, and November 24, 2026. Total: 33 time-out records.

## Audit Logs and Reports

The seeder creates sample activity events for the seeded administrator/staff accounts, academic structure, student profiles, enrollment records, and Visitor Time-Out entries. The Reports module uses all 30 Student records, including Active, Inactive, and Graduated statuses, and both active and inactive portal accounts.

## Reset and safety notes

- Run this dataset only in a local/testing environment.
- It is not invoked by the regular `php artisan db:seed` command.
- It is repeatable and does not duplicate the sample records when run again.
- The recommended presentation setup uses the separate `sims_presentation_db` database.
- Before running `php artisan migrate:fresh`, verify `DB_DATABASE=sims_presentation_db`. Never point that destructive command at the original `sims_db`.
