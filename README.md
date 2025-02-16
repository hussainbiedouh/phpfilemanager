# Windows File Explorer Simulator 🖥️

A modern, feature-rich web-based file management system that mimics Windows Explorer functionality, built in a single PHP file.

## 🌟 Key Features

### File Operations
- ✨ Multiple file selection (Ctrl+Click, Shift+Click, drag selection)
- 📋 Copy, Cut, Paste functionality
- 🗑️ Delete files and folders
- ✏️ Rename files and folders
- 📁 Create new folders
- 🔄 Drag and drop support

### View Options
- 📊 Grid and List view modes
- 🔍 Real-time search functionality
- 💾 Drive space visualization
- 📝 Sortable columns in list view

### User Interface
- 🎯 Windows Explorer-like interface
- 🌙 Dark mode support
- 📱 Responsive design for mobile devices
- 🖼️ File type icons
- 📊 Drive space usage bars

### Keyboard Shortcuts
- `Ctrl + A`: Select all
- `Ctrl + C`: Copy selected
- `Ctrl + X`: Cut selected
- `Ctrl + V`: Paste
- `Delete`: Delete selected
- `Enter`: Open selected
- `Escape`: Clear selection

## 🛠️ Requirements

- PHP 5.6.40 or higher
- Web server (Apache/Nginx)
- UTF-8 support enabled

## ⚡ Installation

1. Copy the single PHP file to your web server
2. Ensure proper file permissions are set
3. Access through your web browser

## 🔒 Security Features

- Path validation and sanitization
- System directory access restrictions
- UTF-8 encoding support
- Error handling with user feedback

## 🌐 Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## 📱 Mobile Support

- Touch-friendly interface
- Mobile-optimized layouts
- Responsive design
- Gesture support

## ⚙️ Configuration

The file manager works out of the box with default settings. You can modify the following sections in the code:

- System directory restrictions
- Allowed file types
- Interface customization
- Path configurations
- Password Protection: To enable password protection, open 'phpfilemanager/index.php' and set the $password variable to the MD5 hash of your desired password (e.g. md5('yourpassword')). Leave it empty for no protection.

## 🎨 Customization

The interface can be customized by modifying the embedded CSS:

- Color schemes
- Icon sizes
- Layout spacing
- Grid/List view properties

## 🛡️ Security Considerations

- Keep PHP updated
- Set proper file permissions
- Restrict access to sensitive directories
- Monitor error logs
- Implement user authentication if needed

## 📝 License

MIT License - feel free to use and modify as needed.

## 👨‍💻 Author

Hussain Biedouh

## 🤝 Contributing

Feel free to submit issues and enhancement requests!

## 📚 Usage Examples

### Basic Navigation
1. Click on drives to explore contents
2. Use breadcrumb navigation
3. Double-click folders to open them
4. Use search bar to find files

### File Operations
1. Select files using Ctrl+Click or Shift+Click
2. Right-click for context menu
3. Drag and drop files to move/copy
4. Use keyboard shortcuts for quick operations

## ⚠️ Known Limitations
- Large directory listings may take time to load (a loading spinner will appear next to the path bar during processing)
- Some operations may be restricted by server permissions
- File preview limited to supported formats

## 🔄 Updates

Check the source repository for updates and improvements.

---

For issues and suggestions, please create an issue in the repository.
