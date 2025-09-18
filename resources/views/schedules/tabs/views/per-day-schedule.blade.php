<!-- Main Content -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <!-- Left: Title -->
        <h2 class="text-xl font-semibold text-gray-800">Class Scheduling</h2>
        <!-- Right: Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('schedules.index', ['tab' => 'class']) }}"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                ← Back to Class Scheduling
            </a>
            <button class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-file-excel"></i>
                <span>Export Excel</span>
            </button>
        </div>
</div>
    <!-- Status and Info -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Status:</span>
            <p class="text-orange-500 font-semibold">Partially Completed</p>
        </div>
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">School:</span>
            <p class="text-gray-800 font-semibold">TOKOGAWA</p>
        </div>
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Date:</span>
            <p class="text-gray-800 font-semibold">September 2, 2025</p>
        </div>
        <div>
            <span class="text-sm text-gray-500 uppercase tracking-wide">Day:</span>
            <p class="text-gray-800 font-semibold">Tuesday</p>
        </div>
</div>
    <!-- Schedule Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Time Slot 1 -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="bg-blue-700 text-white px-4 py-3">
                <h3 class="font-semibold text-center">J3-5A | 8:40 AM</h3>
            </div>
            <div class="p-4">
                <div class="text-center text-sm text-gray-600 font-medium mb-3">TUTORS</div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-sm">
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Faitherine</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Melky</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">John</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Anna</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Marie</div>
                    </div>
                    <div class="text-sm">
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Kath</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Jody</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Martin</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Rudy</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Mercy</div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 pt-3 border-t">
                    <span class="text-sm text-gray-600">Slots: 10/10</span>
                    <button class="editBtn text-orange-500 hover:text-orange-600"
                            data-class="J3-5A" data-time="8:40 AM" data-date="September 2, 2025"><i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
</div>
        <!-- Time Slot 2 -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="bg-blue-700 text-white px-4 py-3">
                <h3 class="font-semibold text-center">J3-4A | 9:40 AM</h3>
            </div>
            <div class="p-4">
                <div class="text-center text-sm text-gray-600 font-medium mb-3">TUTORS</div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-sm">
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Faitherine</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Melky</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">John</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Anna</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Marie</div>
                    </div>
                    <div class="text-sm">
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Kath</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Jody</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Martin</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Rudy</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 pt-3 border-t">
                    <span class="text-sm text-gray-600">Slots: 9/10</span>
                    <button class="text-orange-500 hover:text-orange-600">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
</div>
        <!-- Time Slot 3 -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="bg-blue-700 text-white px-4 py-3">
                <h3 class="font-semibold text-center">J3-6A | 1:00 PM</h3>
            </div>
            <div class="p-4">
                <div class="text-center text-sm text-gray-600 font-medium mb-3">TUTORS</div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="text-sm">
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Faitherine</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Melky</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">John</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Anna</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Marie</div>
                    </div>
                    <div class="text-sm">
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Kath</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Jody</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2">Martin</div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2"></div>
                        <div class="py-2 px-3 bg-gray-50 rounded mb-2"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mt-4 pt-3 border-t">
                    <span class="text-sm text-gray-600">Slots: 8/10</span>
                    <button class="text-orange-500 hover:text-orange-600">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
        </div>
</div>
    <!-- Action Buttons -->
    <div class="flex items-center justify-center space-x-4">
        <button class="bg-orange-400 hover:bg-orange-500 text-white px-6 py-2 rounded-lg font-medium transition-colors">
            Save as Partial
        </button>
        <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
            Save as Final
        </button>
    </div>
</div>

<!-- Background overlay (modal, initially hidden) -->
<div id="editScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <!-- Modal box -->
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
        <!-- Header -->
        <div class="flex justify-between items-center bg-yellow-400 text-black px-4 py-3 rounded-t-lg">
            <h2 class="text-lg font-bold">Edit Schedule</h2>
            <button id="closeModal" class="text-black font-bold text-xl">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 text-sm">
            
            <!-- Class / Time / Date -->
            <div>
            <p><span class="font-semibold">Class:</span> J3-5A</p>
            <div class="flex justify-between">
                <p><span class="font-semibold">Time:</span> 8:40 AM</p>
                <p><span class="font-semibold">Date:</span> September 2, 2025</p>
            </div>
            </div>
            <hr class="my-3">

            <!-- Assigned Tutors -->
            <div class="flex justify-between items-center">
            <span class="font-semibold">Assigned Tutors:</span>
            <select id="addTutorSelect" class="border border-gray-300 rounded px-2 py-1 text-sm">
                <option value="">Add tutor</option>
                <option value="Faithherine">Faithherine</option>
                <option value="Kath">Kath</option>
                <option value="Melky">Melky</option>
                <option value="Jody">Jody</option>
                <option value="John">John</option>
                <option value="Martin">Martin</option>
                <option value="Anna">Anna</option>
                <option value="Rudy">Rudy</option>
                <option value="Marie">Marie</option>
                <option value="Mercy">Mercy</option>
            </select>
            </div>

            <hr class="my-3">

            <!-- Tutor Grid -->
            <div id="tutorGrid" class="grid grid-cols-2 gap-4">
            <!-- Tutors will be inserted here -->
            </div>

            <hr class="my-3">

            <!-- Backup Tutor -->
            <div>
            <span class="font-semibold">Back-up Tutor:</span>
            <select class="border border-gray-300 rounded px-2 py-2 w-full text-sm mt-1">
                <option>Select tutor</option>
            </select>
            </div>

            <hr class="my-3">
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-3 px-6 py-4 border-t">
            <button id="cancelModal" class="px-4 py-2 rounded-md border border-gray-300 text-gray-600">Cancel</button>
            <button class="px-4 py-2 rounded-md bg-green-500 text-white">Save Changes</button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="bg-white rounded-lg shadow-sm p-6">
    <!-- ... your schedule cards and action buttons ... -->
</div>

<!-- Modal -->
<div id="editScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <!-- Modal content here -->
</div>

<!-- Modal Script (put it here) -->
<script>
  const modal = document.getElementById("editScheduleModal");
  const closeBtn = document.getElementById("closeModal");
  const cancelBtn = document.getElementById("cancelModal");

  // Tutor grid & dropdown
  let tutors = ["Faithherine","Kath","Melky","Jody","John","Martin","Anna","Rudy","Marie","Mercy"];
  const tutorGrid = document.getElementById("tutorGrid");
  const addTutorSelect = document.getElementById("addTutorSelect");

  function renderTutors() {
    tutorGrid.innerHTML = "";
    tutors.forEach((tutor,index)=>{
      const div = document.createElement("div");
      div.className="flex justify-between items-center border px-3 py-2 rounded";
      div.innerHTML=`<span>${tutor}</span>
        <button class="text-red-500 font-bold" onclick="removeTutor(${index})">&times;</button>`;
      tutorGrid.appendChild(div);
    });
  }

  function removeTutor(index){
    tutors.splice(index,1);
    renderTutors();
  }

  addTutorSelect.addEventListener("change",function(){
    const selectedTutor=this.value;
    if(selectedTutor && !tutors.includes(selectedTutor)){
      tutors.push(selectedTutor);
      renderTutors();
    }
    this.value="";
  });

  renderTutors();

  // Open modal for each Edit button
  document.querySelectorAll(".editBtn").forEach(btn=>{
    btn.addEventListener("click",()=>{
      document.querySelector("#editScheduleModal p span.font-semibold + span")?.remove();
      const classPara=document.querySelector("#editScheduleModal p");
      classPara.innerHTML=`<span class="font-semibold">Class:</span> ${btn.dataset.class}`;
      const timeDateParas=document.querySelectorAll("#editScheduleModal .flex.justify-between p");
      timeDateParas[0].innerHTML=`<span class="font-semibold">Time:</span> ${btn.dataset.time}`;
      timeDateParas[1].innerHTML=`<span class="font-semibold">Date:</span> ${btn.dataset.date}`;
      modal.classList.remove("hidden");
    });
  });

  // Close modal
  closeBtn.addEventListener("click",()=>modal.classList.add("hidden"));
  cancelBtn.addEventListener("click",()=>modal.classList.add("hidden"));
  modal.addEventListener("click",(e)=>{if(e.target===modal) modal.classList.add("hidden");});
</script>