# ENVIRONMENT_AUDIT.md

## Audit Date
2026-11-11

## Host OS
- Windows 10, Build 19041.6456, 64-bit
- Shell: PowerShell via VS Code

## PHP
- Installed: YES
- Version: 8.2.12 (ZTS, Visual C++ 2019 x64)
- Source: XAMPP, in PATH (`C:\xampp\php\php.exe`)
- Required extensions: all present (bcmath, ctype, curl, dom, fileinfo,
  filter, mbstring, openssl, pcre, PDO, pdo_mysql, session, tokenizer, xml, zip)
- Missing: NONE

## Composer
- Version: 2.11.2 (upgraded from 2.8.12 on 2026-11-11)
- Diagnose: OK

## Node.js / NPM
- Node: v22.19.0 (LTS)
- NPM: 10.9.3
- Path: C:\Program Files\nodejs\

## Python
- System default `python`: 3.13.7 (not used for ANN)
- ANN target: 3.12.8 (invoked via `py -3.12`)
- Other: 3.15.2 (unused)
- venv: OK
- Keras / TensorFlow: not installed yet (Phase 1)

## MariaDB
- Bundle: XAMPP
- Version: 10.4.32-MariaDB
- Service: `mysql` (Running, Automatic)
- Port: 3306
- sql_mode: NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION
  (not strict — Laravel overrides per-connection; my.ini fix recommended)
- EOL: June 2024 (development acceptable, production MUST upgrade)

## Git
- Version: 2.56.0.windows.4
- Configured: Quinc3 <afdilahfariz15@gmail.com>

## Laravel Installer
- Version: 5.25.3

## Disk
- C: free ≈ 38.7 GB

## Repository
- URL: https://github.com/Quinc3/seumpama-pos.git
- Local: C:\xampp\htdocs\seumpama-pos
- State at audit: empty

## Blocker Resolution
| ID | Issue | Resolution |
|---|---|---|
| B1 | Composer 2.8.12 (11 CVE) | RESOLVED → 2.11.2 |
| B2 | MariaDB 10.4.32 EOL | ACCEPTED for dev (user decision) |
| B3 | Python 3.13 + TF risk | RESOLVED → Python 3.12.8 parallel |
| B4 | openssl unverified | RESOLVED → loaded |

## Version Lock (Development)
| Component | Version |
|---|---|
| PHP | 8.2.12 |
| Composer | 2.11.2 |
| Laravel | 12.x (Phase 1) |
| Node / NPM | 22.19.0 / 10.9.3 |
| Python (ANN) | 3.12.8 |
| Database | MariaDB 10.4.32 (dev) |
| Git | 2.56.0 |

## Production Targets (re-verify Phase 11)
| Component | Target |
|---|---|
| PHP | 8.2.x or 8.3.x NTS |
| Database | MariaDB 10.11 LTS or MySQL 8.4 LTS |
| Python | 3.12.x |
| HTTPS | Required |