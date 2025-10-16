# 🎯 Salesmen API

Profesionálna REST API pre správu obchodníkov. Implementované v Laravel s PostgreSQL, kompletne pokryté testami a splňujúce OpenAPI špecifikáciu.

## 🚀 Funkcie

- ✅ **CRUD operácie** pre obchodníkov
- ✅ **Validácia dát** proti codelistám
- ✅ **Paginácia a vyhľadávanie**
- ✅ **Import z CSV** súboru
- ✅ **Kompletná chybová handling**
- ✅ **PHPStan Level 9** - type safety
- ✅ **95.8% test coverage**

## 📋 API Endpoints

### Salesmen
- `GET    /api/salesmen` - Zoznam obchodníkov s pagináciou
- `POST   /api/salesmen` - Vytvorenie nového obchodníka
- `GET    /api/salesmen/{uuid}` - Detail obchodníka
- `PUT    /api/salesmen/{uuid}` - Aktualizácia obchodníka
- `DELETE /api/salesmen/{uuid}` - Vymazanie obchodníka

### Codelists
- `GET    /api/codelists` - Číselníky pre validáciu

## 🛠️ Inštalácia

### Požiadavky
- PHP 8.2+
- PostgreSQL 12+
- Composer

### Kroky inštalácie

1. **Naklonuj repozitár**
   ```bash
   git clone https://github.com/your-username/salesmen-api.git
   cd salesmen-api
