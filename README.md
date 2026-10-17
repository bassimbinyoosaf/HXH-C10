# HXH-C10 — Hunter × Hunter CTF Web Challenge

A hard-tier web exploitation CTF challenge set in the Hunter × Hunter universe.

## Challenge Synopsis
- Title: HXH-C10: Hunter Association Secret Gate & Greed Island Economy
- Category: Web Exploitation / Source Code Reconnaissance & Broken Object-Level Authorization (IDOR)
- Difficulty: Hard
- Theme: Hunter × Hunter
- Port: 30010

## Quick Start

### Standalone PHP
```bash
./start.sh 30010
```
Visit `http://127.0.0.1:30010`

### Docker Compose
```bash
docker-compose up -d --build
```
Access: `http://127.0.0.1:30010` or `http://<SERVER_IP>:30010`

## Walkthrough
See `WALKTHROUGH C10.txt` for complete step-by-step solution.

## Key Points
- Leak credentials from JS → roster file
- IDOR on profile to access other accounts
- P2P transfer via wallet
- Client-side tampering of `hunter_pouch` cookie for balance manipulation
