# 📦 SDrive - Secure Backup System

SDrive is a comprehensive backup solution offering secure storage for photos, contacts, documents, and personal items. Built with **HTML**, **CSS**, **JavaScript**, **PHP**, and **MySQL**, it provides a sleek, modern interface and robust features.

## 🚀 Features

- 📸 **Photo Backup & Gallery:** Upload, view, and manage your photos with a modern gallery interface.
- 📇 **Contact Backup & Display:** Securely store and display contacts in an organized view, similar to documents.
- 🔐 **Personal Vault:** Safely back up and manage important personal items with PIN-based access.
- 📊 **Modern UI/UX:** Clean and responsive interface for a seamless user experience.
- 📁 **File Management:** Upload, view, and manage personal files.
- 🔍 **Persistent Data:** Ensures stored items are accessible after a session ends.

## 🛠️ Technologies Used

- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Database: MySQL

## 📋 Prerequisites

Ensure you have the following installed:

- XAMPP (or any compatible LAMP/LEMP stack)
- PHP 8.0 or higher
- MySQL Database

## 📥 Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/yourusername/sdrive.git
   cd sdrive
   ```

2. Set up the database:

   - Import the `yourdrive.sql` file into your MySQL database:

   ```bash
   mysql -u your_user -p your_database < yourdrive.sql
   ```

3. Configure the connection:

   - Update the database credentials in `config.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_user');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'your_database');
   ```

4. Start the development server:

   - Ensure your XAMPP or local server is running.

   - Access the application at:

   ```
   http://localhost/sdrive/
   ```

## 📚 Usage

1. Register and set a secure PIN.
2. Log in using your PIN to access the vault.
3. Upload and manage your photos, contacts, and personal files securely.

## 🛡️ Security Considerations

- Ensure database credentials are securely stored.
- Use HTTPS in a production environment to encrypt data in transit.
- Implement rate limiting to prevent brute-force attacks.

## 🤝 Contributing

Contributions are welcome! To contribute:

1. Fork the repository.
2. Create a new branch (`feature/your-feature`).
3. Commit your changes.
4. Submit a pull request.

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.

## 📧 Contact

For inquiries or support, contact: (snehasishs39@gmail.com)

---

⭐ **If you find this project helpful, consider giving it a star!**

