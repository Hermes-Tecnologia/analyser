# Analyser

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

```bash
composer require hermes-tecnologia/analyser
```

## Usage

Crie uma instância da classe Analysis passando o valor a ser analisado:

```php
use HermesTecnologia\Analyser\Analysis;

$analysis = new Analysis('valor para análise');

ou usar o helper

$analysis = analyse('valor para análise')->sanitize();
```

### Métodos Disponíveis

#### Métodos de Formatação
- **cleanNumber()** - Remove caracteres não numéricos do valor
- **onlyNumbers()** - Retorna apenas os números do valor
- **sanitize()** - Limpa o valor removendo acentos e caracteres especiais
- **clearString()** - Normaliza caracteres com acentos para seus equivalentes sem acento
- **clearStringSpecialChar($extra = false)** - Remove caracteres especiais do texto
- **ansi()** - Converte caracteres Unicode para formato ANSI

#### Métodos para Mascaramento
- **hideEmail($show = 2)** - Oculta parte do email, mostrando apenas os primeiros caracteres
- **hideData($show = 4, $from_end = false)** - Oculta parte do valor com asteriscos
- **maskCNPJ()** - Formata um número como CNPJ (xx.xxx.xxx/xxxx-xx)
- **maskCPF()** - Formata um número como CPF (xxx.xxx.xxx-xx)
- **maskPhone()** - Formata um número como telefone, detectando o formato correto
- **boleto()** - Formata uma linha digitável de boleto
- **maskerize($mask = '####.####', $len = 8)** - Aplica uma máscara personalizada a um valor
- **maskerizeVal($mask = '####.####', $len = 8)** - Similar ao maskerize, mas retorna null se o tamanho for incorreto

#### Métodos de Validação
- **validateCnpj()** - Valida se o valor é um CNPJ válido
- **getCnpj()** - Retorna o CNPJ se for válido, ou null caso contrário
- **validateCpf()** - Valida se o valor é um CPF válido

#### Métodos de Conversão
- **get()** - Retorna o valor atual
- **toDecimal()** - Converte o valor para decimal
- **dateToSql()** - Converte data no formato dd/mm/yyyy para formato SQL (yyyy-mm-dd)
- **sqlToDate($format = 'd/m/Y H:i:s')** - Converte data SQL para o formato especificado
- **getInitials()** - Retorna as iniciais de um nome completo

#### Outros Métodos
- **getWeekPeriod()** - Retorna o período da semana para uma data

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

```bash
composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author@email.com instead of using the issue tracker.

## Credits

- [Author Name][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/hermes-tecnologia/analyser.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/hermes-tecnologia/analyser.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/hermes-tecnologia/analyser/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/hermes-tecnologia/analyser
[link-downloads]: https://packagist.org/packages/hermes-tecnologia/analyser
[link-travis]: https://travis-ci.org/hermes-tecnologia/analyser
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/hermes-tecnologia
[link-contributors]: ../../contributors
