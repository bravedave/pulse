# DTO Creation Instructions for Pulse Project

To create a new DTO (Data Transfer Object) in the `src/pulse/dao/dto/` folder, follow these steps:

1. **Class Naming:**
   - The class name should match the table name in the database (e.g., `pulse_state`).
   - Use lowercase with underscores for consistency with table names.
   - The filename should be `ClassName.php` (e.g., `pulse_state.php` or `PulseStateDto.php`).

2. **Namespace:**
   - Use the namespace: `pulse\dao\dto`.

3. **Fields:**
   - Add a public property for each field in the corresponding database table.
   - Always include an `id` property (default `0`).
   - Use PHP type declarations for each property (e.g., `int`, `string`).
   - Set a default value for each property:
     - `int` fields: default to `0`.
     - `string` fields: default to an empty string `''`.
     - For nullable fields, use `?type` and default to `null`.

4. **Constructor:**
   - Do not include a constructor unless special initialization is required.

5. **Example:**

```php
<?php
namespace pulse\dao\dto;

class pulse_state {
  public $id = 0;
  public string $created = '';
  public string $updated = '';
  public int $pulse_id = 0;
  public int $users_id = 0;
  public int $seen = 0;
}
```

6. **Location:**
   - Save the file in `src/pulse/dao/dto/`.

---

**Note:**

- Always check the corresponding table definition in `src/pulse/dao/db/` for the correct fields and types.
- If the table structure changes, update the DTO accordingly.
