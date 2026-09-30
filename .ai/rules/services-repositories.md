---
paths:
  - 'app/{Services,Repositories}/**'
---

# Services Repositories

## Suffissi coerenti sui metodi di service e repository
Dentro una classe (e fra service e repository) i metodi seguono un solo stile di nome. Se un metodo che riceve un id usa il suffisso `ById`, lo usano tutti quelli che ricevono un id: `findById`, `updateById`, `deleteById`, mai `update(string $id)` accanto a `deleteById`. Il suffisso descrive il parametro: un metodo che riceve il modello già trovato (`update(Product $product)`) non lo prende.

## PHPDoc su ogni metodo dei contract
Ogni metodo delle interfacce in `Contracts/` (service e repository) ha il suo PHPDoc in italiano: cosa fa e cosa restituisce, più `@throws` e i generici dove servono. Le implementazioni non lo ripetono: `{@inheritdoc}` su ogni metodo che implementa il contract.
