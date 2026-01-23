# Testing and Documentation Guide

This guide covers testing the installation script and creating additional documentation materials.

## Installation Testing

### Testing on Ubuntu 22.04

1. **Prepare Clean VM**
   ```bash
   # Get fresh Ubuntu 22.04 LTS
   # Minimum requirements: 2GB RAM, 20GB disk, 2 CPU cores
   ```

2. **Run Installation**
   ```bash
   # Download from main branch once merged
   wget https://raw.githubusercontent.com/i4edubd/ispsolution/main/install.sh
   chmod +x install.sh
   
   # Test basic installation
   sudo bash install.sh
   
   # Or test with all features
   export DOMAIN_NAME="test.example.com"
   export EMAIL="admin@example.com"
   export SETUP_SSL="yes"
   export SWAP_SIZE="2G"
   sudo bash install.sh
   ```

3. **Verify Installation**
   ```bash
   # Check services
   systemctl status php8.2-fpm
   systemctl status nginx
   systemctl status mysql
   systemctl status redis-server
   systemctl status freeradius
   systemctl status openvpn@server
   
   # Check swap
   free -h | grep Swap
   
   # Check credentials
   cat /root/ispsolution-credentials.txt
   
   # Test web access
   curl -I http://localhost
   ```

### Testing on Ubuntu 24.04

Same steps as Ubuntu 22.04 above.

### Common Test Scenarios

**Scenario 1: Basic Installation**
```bash
# Fresh VM, no customization
sudo bash install.sh
# Expected: All services running, localhost access works
```

**Scenario 2: With Custom Domain**
```bash
export DOMAIN_NAME="isp.example.com"
sudo bash install.sh
# Expected: Nginx configured for custom domain
```

**Scenario 3: With SSL**
```bash
export DOMAIN_NAME="isp.example.com"
export EMAIL="admin@example.com"
export SETUP_SSL="yes"
sudo bash install.sh
# Expected: Let's Encrypt certificate installed, HTTPS works
```

**Scenario 4: Custom Swap**
```bash
export SWAP_SIZE="4G"
sudo bash install.sh
# Expected: 4GB swap created and active
```

**Scenario 5: Subdomain Creation**
```bash
# After installation
sudo create-tenant-subdomain.sh tenant1 123
# Expected: tenant1.yourdomain.com accessible
```

### Test Checklist

- [ ] PHP 8.2 installed and running
- [ ] MySQL 8.0 installed and accessible
- [ ] Redis running
- [ ] Nginx serving application
- [ ] FreeRADIUS configured with MySQL
- [ ] OpenVPN server running
- [ ] Swap memory active
- [ ] Application accessible via browser
- [ ] Demo accounts can login
- [ ] Database migrations completed
- [ ] Firewall rules applied
- [ ] SSL certificate installed (if enabled)
- [ ] Subdomain creation script works
- [ ] Cron job for Laravel scheduler added

## Creating Video Tutorials

### Equipment Needed

- **Screen Recording Software**:
  - OBS Studio (free, open-source)
  - Camtasia
  - ScreenFlow (Mac)
  - SimpleScreenRecorder (Linux)

- **Microphone**: Clear audio is essential

- **Script**: Prepare what you'll say

### Video Tutorial Structure

Create one video per role (8 videos total):

#### 1. Developer Guide Tutorial (15-20 min)
**Topics to Cover**:
- Installation and setup
- Development environment
- Running tests
- Debugging
- Deployment

**Script Outline**:
```
1. Introduction (1 min)
   - Welcome, what we'll cover
   
2. Installation (5 min)
   - Show install.sh running
   - Explain what's happening
   
3. Development Setup (5 min)
   - IDE setup
   - Running locally
   - Database access
   
4. Common Tasks (5 min)
   - Creating migrations
   - Running tests
   - Debugging
   
5. Deployment (3 min)
   - Production deployment
   - Best practices
   
6. Wrap-up (1 min)
   - Resources, next steps
```

#### 2. Super Admin Guide Tutorial (10-15 min)
**Topics to Cover**:
- Login and dashboard
- Creating admins
- Managing tenants
- Viewing reports

#### 3. Admin Guide Tutorial (20-25 min)
**Topics to Cover**:
- Dashboard overview
- Adding customers
- Managing packages
- Billing operations
- Network configuration
- Team management

#### 4. Operator Guide Tutorial (15-20 min)
**Topics to Cover**:
- Field operations
- Customer installations
- Payment collection
- Using mobile app

#### 5. Sub-Operator Guide Tutorial (10-15 min)
**Topics to Cover**:
- Customer management
- Payment collection
- Support tickets

#### 6. Manager Guide Tutorial (10-15 min)
**Topics to Cover**:
- Reports and analytics
- Team monitoring
- Performance metrics

#### 7. Staff Guide Tutorial (10-15 min)
**Topics to Cover**:
- Daily tasks
- Data entry
- Support tickets

#### 8. Customer Guide Tutorial (10-15 min)
**Topics to Cover**:
- Customer portal
- Making payments
- Viewing usage
- Getting support

### Recording Tips

1. **Prepare**:
   - Test your setup
   - Write a script
   - Practice once
   - Have a glass of water nearby

2. **During Recording**:
   - Speak clearly and slowly
   - Pause between sections
   - Show cursor movements clearly
   - Use zoom for important details

3. **Post-Production**:
   - Cut mistakes
   - Add intro/outro
   - Add annotations
   - Export in HD (1080p)

### Publishing

- **YouTube**: Upload unlisted/public
- **Vimeo**: Professional hosting
- **Documentation**: Link in guides

## Adding Screenshots to User Guides

### Required Screenshots

For each guide, capture:

1. **Login Page**
   - Clean, shows login form

2. **Dashboard**
   - Main dashboard view
   - Key metrics visible

3. **Key Features** (3-5 screenshots per role):
   - Most important screens for that role
   - Common workflows
   - Forms and dialogs

### Screenshot Guidelines

**Technical Requirements**:
- **Resolution**: 1920x1080 or 1440x900
- **Format**: PNG (lossless)
- **File size**: Optimize to < 500KB
- **Browser**: Chrome/Firefox, latest version

**Content Guidelines**:
- Use demo data (not real customer info)
- Clean browser (no bookmarks showing)
- Full screen capture or specific section
- Highlight important elements with red boxes/arrows

**Naming Convention**:
```
docs/guides/screenshots/
├── developer/
│   ├── 01-dashboard.png
│   ├── 02-code-editor.png
│   └── 03-deployment.png
├── admin/
│   ├── 01-dashboard.png
│   ├── 02-add-customer.png
│   ├── 03-packages.png
│   └── 04-billing.png
└── customer/
    ├── 01-login.png
    ├── 02-dashboard.png
    └── 03-payment.png
```

### Adding Screenshots to Markdown

```markdown
## Dashboard Overview

After logging in, you'll see the main dashboard:

![Admin Dashboard](screenshots/admin/01-dashboard.png)

The dashboard shows:
- Total customers
- Monthly revenue
- Active sessions
```

### Tools for Screenshots

- **Linux**: GNOME Screenshot, Flameshot
- **Mac**: Cmd+Shift+4, Skitch
- **Windows**: Snipping Tool, Greenshot
- **Browser**: Built-in developer tools
- **Annotation**: Skitch, Annotate

## Generating PDF Versions

### Using Pandoc

**Install Pandoc**:
```bash
# Ubuntu/Debian
sudo apt-get install pandoc texlive-xetex

# Mac
brew install pandoc basictex

# Windows
# Download from https://pandoc.org/installing.html
```

**Generate PDFs**:
```bash
cd docs/guides

# Single guide
pandoc DEVELOPER_GUIDE.md -o DEVELOPER_GUIDE.pdf \
  --pdf-engine=xelatex \
  -V geometry:margin=1in \
  -V fontsize=11pt

# All guides
for file in *.md; do
  pandoc "$file" -o "${file%.md}.pdf" \
    --pdf-engine=xelatex \
    -V geometry:margin=1in \
    -V fontsize=11pt \
    -V colorlinks=true
done
```

**With Custom Styling**:
```bash
pandoc DEVELOPER_GUIDE.md -o DEVELOPER_GUIDE.pdf \
  --pdf-engine=xelatex \
  -V geometry:margin=1in \
  -V fontsize=11pt \
  -V mainfont="DejaVu Sans" \
  -V colorlinks=true \
  --toc \
  --toc-depth=2
```

### Using Alternative Tools

**Markdown to PDF (Node.js)**:
```bash
npm install -g markdown-pdf
markdown-pdf DEVELOPER_GUIDE.md
```

**Using Grip** (GitHub-style):
```bash
pip install grip
grip DEVELOPER_GUIDE.md --export DEVELOPER_GUIDE.html
# Then print to PDF from browser
```

### PDF Organization

Create PDF directory:
```bash
docs/
├── guides/
│   ├── DEVELOPER_GUIDE.md
│   └── ...
└── pdf/
    ├── DEVELOPER_GUIDE.pdf
    ├── SUPERADMIN_GUIDE.pdf
    ├── ADMIN_GUIDE.pdf
    ├── OPERATOR_GUIDE.pdf
    ├── SUBOPERATOR_GUIDE.pdf
    ├── MANAGER_GUIDE.pdf
    ├── STAFF_GUIDE.pdf
    └── CUSTOMER_GUIDE.pdf
```

### Batch Generation Script

Create `generate-pdfs.sh`:
```bash
#!/bin/bash

GUIDE_DIR="docs/guides"
PDF_DIR="docs/pdf"

mkdir -p "$PDF_DIR"

for guide in "$GUIDE_DIR"/*.md; do
  filename=$(basename "$guide" .md)
  echo "Generating $filename.pdf..."
  
  pandoc "$guide" -o "$PDF_DIR/$filename.pdf" \
    --pdf-engine=xelatex \
    -V geometry:margin=1in \
    -V fontsize=11pt \
    -V mainfont="DejaVu Sans" \
    -V colorlinks=true \
    -V linkcolor=blue \
    --toc \
    --toc-depth=2 \
    -V title="$filename" \
    -V date="$(date +%Y-%m-%d)"
done

echo "All PDFs generated in $PDF_DIR/"
```

Make executable and run:
```bash
chmod +x generate-pdfs.sh
./generate-pdfs.sh
```

## Quality Checklist

### For Videos
- [ ] Audio is clear
- [ ] No background noise
- [ ] Screen is visible and readable
- [ ] Cursor movements are smooth
- [ ] Important areas are highlighted
- [ ] Pace is appropriate (not too fast)
- [ ] Intro/outro added
- [ ] Uploaded and linked in documentation

### For Screenshots
- [ ] High resolution (1080p minimum)
- [ ] Demo data used (no real info)
- [ ] Clean interface (no clutter)
- [ ] Important elements highlighted
- [ ] Properly named and organized
- [ ] Added to markdown files
- [ ] File sizes optimized

### For PDFs
- [ ] All guides converted
- [ ] Table of contents included
- [ ] Links work
- [ ] Formatting is clean
- [ ] Images embedded properly
- [ ] Page breaks appropriate
- [ ] Stored in `docs/pdf/` folder
- [ ] Listed in documentation index

## Timeline Suggestion

**Week 1**: Testing
- Day 1-2: Test Ubuntu 22.04
- Day 3-4: Test Ubuntu 24.04
- Day 5: Fix any issues found

**Week 2**: Screenshots
- Day 1: Setup demo environment
- Day 2-3: Capture all screenshots
- Day 4: Add to documentation
- Day 5: Review and adjust

**Week 3**: Videos
- Day 1: Setup recording environment
- Day 2-3: Record tutorials (4 videos)
- Day 4-5: Record remaining (4 videos)

**Week 4**: Post-Production
- Day 1-3: Edit videos
- Day 4: Generate PDFs
- Day 5: Final review and publish

## Resources

### Screen Recording
- [OBS Studio](https://obsproject.com/) - Free, open-source
- [Loom](https://www.loom.com/) - Quick screen recordings
- [Camtasia](https://www.techsmith.com/video-editor.html) - Professional

### Image Editing
- [GIMP](https://www.gimp.org/) - Free Photoshop alternative
- [Inkscape](https://inkscape.org/) - Vector graphics
- [Flameshot](https://flameshot.org/) - Screenshot + annotation

### PDF Generation
- [Pandoc](https://pandoc.org/) - Universal document converter
- [wkhtmltopdf](https://wkhtmltopdf.org/) - HTML to PDF
- [Prince](https://www.princexml.com/) - Professional PDF

## Support

For questions about testing or documentation:
- Check this guide
- Review existing documentation
- Open GitHub issue
- Contact development team

---

**Last Updated**: January 23, 2026  
**Status**: Ready for implementation
