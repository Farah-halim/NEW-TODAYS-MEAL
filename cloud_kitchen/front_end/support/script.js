
document.addEventListener('DOMContentLoaded', function() {
    // Section Navigation
    const actionBtns = document.querySelectorAll('.action-btn');
    const sections = document.querySelectorAll('.support-section');
  
    // Initially hide all sections except the first one
    sections.forEach((section, index) => {
      if (index === 0) {
        section.style.display = 'block';
        section.classList.add('active');
      } else {
        section.style.display = 'none';
        section.classList.remove('active');
      }
    });
  
    // Make sure DOM elements exist before adding listeners
    const newTicketSection = document.getElementById('new-ticket');
    const faqSection = document.getElementById('faq');
    if (newTicketSection && faqSection) {
      newTicketSection.style.display = 'none';
      faqSection.style.display = 'none';
    }
    
    actionBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const targetSection = btn.dataset.section;
        
        const targetElement = document.getElementById(targetSection);
        if (targetElement) {
          // Remove active class from all buttons and sections
          actionBtns.forEach(b => b.classList.remove('active'));
          sections.forEach(s => {
            s.classList.remove('active');
            s.style.display = 'none';
          });
          
          // Add active class to clicked button and show target section
          btn.classList.add('active');
          targetElement.classList.add('active');
          targetElement.style.display = 'block';
        }
      });
    });
  
    // Ticket Tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    if (tabBtns.length > 0) {
      const activeTickets = document.getElementById('activeTickets');
      const resolvedTickets = document.getElementById('resolvedTickets');
      
      tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          const tab = btn.dataset.tab;
          
          tabBtns.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          
          if (tab === 'active' && activeTickets && resolvedTickets) {
            activeTickets.classList.add('visible');
            resolvedTickets.classList.remove('visible');
          } else if (activeTickets && resolvedTickets) {
            activeTickets.classList.remove('visible');
            resolvedTickets.classList.add('visible');
          }
        });
      });
    }
  
    // Custom Issue Type
    const issueTypeSelect = document.getElementById('issueType');
    const customIssueGroup = document.getElementById('customIssueGroup');
    
    if (issueTypeSelect && customIssueGroup) {
      issueTypeSelect.addEventListener('change', function() {
        if (this.value === 'other') {
          customIssueGroup.style.display = 'flex';
          customIssueGroup.querySelector('input').required = true;
        } else {
          customIssueGroup.style.display = 'none';
          customIssueGroup.querySelector('input').required = false;
        }
      });
    }
  
    // FAQ Item Expansion and Search
    const faqItems = document.querySelectorAll('.faq-item');
    const faqSearch = document.querySelector('.faq-search');
    
    faqItems.forEach(item => {
      item.addEventListener('click', () => {
        const currentlyActive = document.querySelector('.faq-item.active');
        if (currentlyActive && currentlyActive !== item) {
          currentlyActive.classList.remove('active');
        }
        item.classList.toggle('active');
      });
    });
  
    if (faqSearch) {
      faqSearch.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        faqItems.forEach(item => {
          const question = item.querySelector('.faq-question').textContent.toLowerCase();
          const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
          
          if (question.includes(searchTerm) || answer.includes(searchTerm)) {
            item.style.display = 'block';
          } else {
            item.style.display = 'none';
          }
        });
      });
    }
  });
  