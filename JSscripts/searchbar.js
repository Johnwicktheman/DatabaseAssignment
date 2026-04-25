    //Main Search and Sort Function
    function applyFilters() {
        let searchInput = document.getElementById("jsSearch").value.toLowerCase();
        let sortType = document.getElementById("jsSort").value;
        let tbody = document.querySelector("#searchTable tbody");
        let rows = Array.from(tbody.querySelectorAll(".search-row"));
        let noResultsRow = document.getElementById("noResultsRow");

       rows.sort((a, b) => {
            let aID = parseInt(a.getAttribute("data-id"));
            let bID = parseInt(b.getAttribute("data-id"));

            if (sortType === 'newest') {
                return bID - aID; // Sort by ID descending
            } 
            else if (sortType === 'oldest') {
                return aID - bID; // Sort by ID ascending
            } 
            else if (sortType === 'no_record') {
                let aHasRec = parseInt(a.getAttribute("data-has-record"));
                let bHasRec = parseInt(b.getAttribute("data-has-record"));
                
                // If both have records or both don't, sort by ID ascending
                if (aHasRec === bHasRec) {
                    return aID - bID;
                }
                // Otherwise, put the one without a record (0) before the one with a record (1)
                return aHasRec - bHasRec; 
            }
        });

        let visibleCount= 0;

        //Re-attach rows to the table in the new sorted order and apply serach filter
        rows.forEach(row => {
            tbody.appendChild(row);
            
            // Check if it matches the search bar
            let idTxt = row.getAttribute("data-id").toLowerCase();
            let nameTxt = row.getAttribute("data-name").toLowerCase();
            
            if (idTxt.includes(searchInput) || nameTxt.includes(searchInput)) {
                row.style.display = "";
                visibleCount++; // Increment if row is shown            
            } else {
                row.style.display = "none";
            }
        });

        if (visibleCount === 0) {
            noResultsRow.style.display = ""; 
            tbody.appendChild(noResultsRow);
        } else {
            noResultsRow.style.display = "none";
        }
    }