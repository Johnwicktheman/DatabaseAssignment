    // Sample data – in a real app this would be rendered by PHP from MySQL
    var users = [
      { id: 'S10001', name: 'Ahmad Faris',     email: 'ahmad.faris@student.edu',   role: 'Student' },
      { id: 'S10002', name: 'Nur Hidayah',     email: 'nur.hidayah@student.edu',   role: 'Student' },
      { id: 'S10003', name: 'Raj Kumar',       email: 'raj.kumar@student.edu',     role: 'Student' },
      { id: 'S10004', name: 'Li Wei',          email: 'li.wei@student.edu',        role: 'Student' },
      { id: 'A00001', name: 'Admin User',      email: 'admin@university.edu',      role: 'Admin'   },
    ];

    var editingIndex = null;

    function renderTable(data) {
        var tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

      if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">No records found.</td></tr>';
        return;
      }
    }