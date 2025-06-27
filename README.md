# Trait `ActionPack` @evlimma

The `ActionPack` trait provides a reusable set of methods for models extending a `DataLayer`-like class (such as `CoffeeCode\DataLayer`).  
It encapsulates common logic for dynamic filtering, paginated queries, and frequent data access operations.

A trait `ActionPack` fornece um conjunto reutilizável de métodos para modelos que estendem uma classe do tipo `DataLayer` (como o `CoffeeCode\DataLayer`).  
Ela encapsula a lógica comum para criação dinâmica de filtros, buscas paginadas e operações frequentes de acesso a dados.

---

### Highlights

- Easy to set up (Fácil de configurar)

---

## Installation

Data Layer is available via Composer:

```bash
"evlimma/actionpack": "1.0.*"
```

or run

```bash
composer require evlimma/actionpack
```

---

# Documentation

## Requirements / Requisitos
To use this trait properly, the class that uses it must:
- Extend a class that provides methods like `find()`, `count()`, `limit()`, `offset()`, `order()`, and `fetch()`.
- Define the `$primary` property (primary key field name).
- Have access to the `DynamicFilter` class.
- Have access to the model `OrderColItem` (used for dynamic column filters).

  
Para usar essa trait corretamente, a classe que a utiliza deve:
- Estender uma classe que forneça métodos como `find()`, `count()`, `limit()`, `offset()`, `order()` e `fetch()`.
- Definir a propriedade `$primary` (nome do campo chave primária).
- Ter acesso à classe `DynamicFilter`.
- Ter acesso ao model `OrderColItem` (usado para filtros dinâmicos de colunas).

---

## Available Methods  

### `message(): ?Message`  
Returns the current message instance. Useful for returning success or error feedback.

Retorna a instância atual da mensagem. Útil para retornar mensagens de sucesso ou erro.

---

### `addfields(): ?object`  
Builds dynamic filters based on the `$addfields` array using the `DynamicFilter` class.  
It also includes extra column filters from the associated model.

Cria filtros dinâmicos com base no array `$addfields`, usando a classe `DynamicFilter`.  
Também adiciona filtros extras com base nas colunas do modelo associado.

---

### `listEntity(): ?array`  
Fetches all records from the current entity using dynamic filters.

Busca todos os registros da entidade atual usando filtros dinâmicos.

---

### `findByPag(?array $dataArr = null, ?int $itensPerPage = ITEMS_PER_PAGE, int $start = 1): ?object`  
Returns a paginated set of results using filters.  
Returns an object with:
- `findCount`: total matched records
- `findFetch`: current page data

Retorna um conjunto paginado de resultados usando filtros.  
Retorna um objeto com:
- `findCount`: total de registros encontrados
- `findFetch`: dados da página atual

---

### `findByActive(): ?array`  
Returns all records with `status = 1`. Useful to fetch only active entries.

Retorna todos os registros com `status = 1`. Útil para buscar apenas os registros ativos.

---

### `findByKey(int $id): ?self`  
Finds a single record by its primary key.

Busca um único registro com base na chave primária.

---

### `findByFields(?array $filter): ?array`  
Finds records by an associative array of filters, applying `=` conditions on each field.

Busca registros com base em um array associativo de filtros, aplicando condições de igualdade (`=`) em cada campo.

---

###### For details on how to use the ActionPack, see the sample folder with details in the component directory

Para mais detalhes sobre como usar o ActionPack, veja a pasta de exemplo com detalhes no diretório do componente

---

## Benefits / Benefícios
- Reduces duplicated logic across multiple models.  
- Centralized and flexible filtering system.  
- Ready-to-use pagination and query patterns.  
- Easier maintenance and cleaner code.

- Reduz a duplicação de lógica entre diversos modelos.  
- Sistema de filtros centralizado e flexível.  
- Padrões de paginação e busca prontos para uso.  
- Código mais limpo e fácil de manter.

---

## Contributing

Please see [CONTRIBUTING](https://github.com/evlimma/actionpack/blob/master/CONTRIBUTING.md) for details.

## Support

###### Security: If you discover any security related issues, please email contato@codigospace.com.br instead of using the issue tracker.

Se você descobrir algum problema relacionado à segurança, envie um e-mail para contato@codigospace.com.br em vez de usar o rastreador de problemas.

Thank you


## Credits

- [Everton A. Lima](https://github.com/evlimma) (Developer)
- [All Contributors](https://github.com/evlimma/actionpack/graphs/contributors) (This Rock)

## License

The MIT License (MIT). Please see [License File](https://github.com/evlimma/actionpack/blob/master/LICENSE) for more
information.
