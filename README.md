
# VIP Learn Audit Plugin

This plugin provides a suite of WP-CLI commands for auditing Sensei courses. It is designed to help course authors maintain consistent formatting in course titles, headings, and content.

## Features

- Audit lesson titles and headings for **title case** and **sentence case** compliance.
- Identify incorrect word usage, such as trademarks or special capitalization cases.
- Audit content for basic punctuation issues (e.g., missing punctuation in paragraphs, list items, and blockquotes).
- Validate whether a given string adheres to title case or sentence case standards.

## Installation

1. Clone this repository into your WordPress plugins directory:
   ```bash
   git clone https://github.com/Automattic/vip-learn-audit.git
   ```
2. Activate the plugin:
   ```bash
   wp plugin activate vip-learn-audit
   ```

## Commands

### 1. Audit Course Titles for Title Case Compliance
```bash
wp vip-learn audit course-title-case <course_id>
```
**Description:** Checks the titles of lessons and headings in a specified course for title case issues.

**Options:**
- `<course_id>`: The ID of the Sensei course to audit.

**Example:**
```bash
wp vip-learn audit course-title-case 123
```

---

### 2. Audit Course Titles for Sentence Case Compliance
```bash
wp vip-learn audit course-sentence-case <course_id>
```
**Description:** Checks the titles of lessons and headings in a specified course for sentence case issues.

**Options:**
- `<course_id>`: The ID of the Sensei course to audit.

**Example:**
```bash
wp vip-learn audit course-sentence-case 123
```

---

### 3. Audit Word Instances
```bash
wp vip-learn audit word-instances <course_id>
```
**Description:** Identifies incorrect word usage, such as trademarks or other predefined special cases, within lesson content and headings.

**Options:**
- `<course_id>`: The ID of the Sensei course to audit.

**Example:**
```bash
wp vip-learn audit word-instances 123
```

---

### 4. Audit Course Punctuation
```bash
wp vip-learn audit course-punctuation <course_id>
```
**Description:** Checks paragraphs, list items, and blockquotes for missing or incorrect punctuation.

**Options:**
- `<course_id>`: The ID of the Sensei course to audit.

**Example:**
```bash
wp vip-learn audit course-punctuation 123
```

---

### 5. Check String for Title Case
```bash
wp vip-learn audit check-title-case-string "<string>"
```
**Description:** Verifies if the provided string adheres to title case.

**Options:**
- `<string>`: The string to check.

**Example:**
```bash
wp vip-learn audit check-title-case-string "This Is a Title"
```

---

### 6. Check String for Sentence Case
```bash
wp vip-learn audit check-sentence-case-string "<string>"
```
**Description:** Verifies if the provided string adheres to sentence case.

**Options:**
- `<string>`: The string to check.

**Example:**
```bash
wp vip-learn audit check-sentence-case-string "This is a sentence."
```

---

## Development

### Prerequisites
- WordPress environment
- Sensei LMS plugin for testing courses

### Adding Custom Cases
You can extend the audit logic by customizing the `Punctuation` or `TitleCase`/`SentenceCase` utilities as required.

### Register Commands
The commands are registered in the `Command` class using the `WP_CLI::add_command` method.

---

## License

This plugin is licensed under GPL-2.0-or-later. See the [LICENSE](LICENSE) file for details.

---

## Contributing

Contributions are welcome! Please open an issue or submit a pull request to the repository.

---

## Author

- **Automattic - WordPress VIP - VIP Learn**
- [GitHub Repository](https://github.com/Automattic/vip-learn-audit)
