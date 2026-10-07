/**
 * JavaScript pour la gestion des fréquentations
 */

// Types d'utilisation (20 types) avec logos
const typesUtilisation = [
  {
    id: 1,
    nom: "France Travail",
    code: "TEST1",
    logo: "assets/images/logos/france-travail.png",
    fallback: "💼",
  },
  {
    id: 2,
    nom: "CAF",
    code: "TEST2",
    logo: "assets/images/logos/caf.png",
    fallback: "🏠",
  },
  {
    id: 3,
    nom: "AMELI",
    code: "TEST3",
    logo: "assets/images/logos/ameli.png",
    fallback: "🏥",
  },
  {
    id: 4,
    nom: "Demande logement social",
    code: "TEST4",
    logo: "assets/images/logos/logement.png",
    fallback: "🏘️",
  },
  {
    id: 5,
    nom: "LADOM",
    code: "TEST5",
    logo: "assets/images/logos/ladom.png",
    fallback: "🌍",
  },
  {
    id: 6,
    nom: "Retraite",
    code: "TEST6",
    logo: "assets/images/logos/retraite.png",
    fallback: "👴",
  },
  {
    id: 7,
    nom: "Impôts",
    code: "TEST7",
    logo: "assets/images/logos/impots.png",
    fallback: "💰",
  },
  {
    id: 8,
    nom: "ANTS",
    code: "TEST8",
    logo: "assets/images/logos/ants.png",
    fallback: "🆔",
  },
  {
    id: 9,
    nom: "Paiements factures",
    code: "TEST9",
    logo: "assets/images/logos/paiement.png",
    fallback: "💳",
  },
  {
    id: 10,
    nom: "Formulaire identité",
    code: "TEST10",
    logo: "assets/images/logos/identite.png",
    fallback: "📄",
  },
  {
    id: 11,
    nom: "CESU URSSAF",
    code: "TEST11",
    logo: "assets/images/logos/urssaf.png",
    fallback: "💼",
  },
  {
    id: 12,
    nom: "MDPH Mobilité Inclusion",
    code: "TEST12",
    logo: "assets/images/logos/mdph.png",
    fallback: "♿",
  },
  {
    id: 13,
    nom: "Banque Assurance",
    code: "TEST13",
    logo: "assets/images/logos/banque.png",
    fallback: "🏦",
  },
  {
    id: 14,
    nom: "Education Bourses",
    code: "TEST14",
    logo: "assets/images/logos/education.png",
    fallback: "🎓",
  },
  {
    id: 15,
    nom: "Formalités voyage",
    code: "TEST15",
    logo: "assets/images/logos/voyage.png",
    fallback: "✈️",
  },
  {
    id: 16,
    nom: "Timbres fiscaux",
    code: "TEST16",
    logo: "assets/images/logos/timbres.png",
    fallback: "📮",
  },
  {
    id: 17,
    nom: "Amendes",
    code: "TEST17",
    logo: "assets/images/logos/amendes.png",
    fallback: "🚨",
  },
  {
    id: 18,
    nom: "Casier judiciaire",
    code: "TEST18",
    logo: "assets/images/logos/justice.png",
    fallback: "⚖️",
  },
  {
    id: 19,
    nom: "Carte de séjour Immigration",
    code: "TEST19",
    logo: "assets/images/logos/immigration.png",
    fallback: "🛂",
  },
  {
    id: 20,
    nom: "Autres",
    code: "TEST20",
    logo: "assets/images/logos/autres.png",
    fallback: "📋",
  },
];

let utilisationsSelectionnees = [];

function rechercherUsager() {
  document.getElementById("modalUsager").style.display = "block";
  rechercherUsagers();
}

function rechercherUsagers() {
  const recherche = document.getElementById("rechercheUsager").value;

  fetch(`api/usagers.php?recherche=${encodeURIComponent(recherche)}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const listeElement = document.getElementById("listeUsagers");
        listeElement.innerHTML = ""; // Vider la liste

        if (data.usagers.length === 0) {
          const noData = document.createElement("ul");
          noData.className = "usager-list";
          const item = document.createElement("li");
          item.className = "no-data";
          item.textContent = "Aucun usager trouvé";
          noData.appendChild(item);
          listeElement.appendChild(noData);
        } else {
          const ul = document.createElement("ul");
          ul.className = "usager-list";

          data.usagers.forEach((usager) => {
            const li = document.createElement("li");

            // Créer les données sécurisées (non échappées pour les attributs data)
            const numInscription = usager.NUM_INSCRIPTION;
            const civilite = String(usager.CIVILITE || "");
            const noms = String(usager.NOMS_PRENOMS);
            const ville = String(usager.CODE_VILLE || "");

            // Stocker les données en attributs data-* (plus sûr)
            li.setAttribute("data-num-inscription", numInscription);
            li.setAttribute("data-civilite", civilite);
            li.setAttribute("data-noms", noms);
            li.setAttribute("data-ville", ville);

            // Construire le contenu avec textContent (échappe automatiquement)
            const strong = document.createElement("strong");
            strong.textContent = `${civilite} ${noms}`.trim();

            const small = document.createElement("small");
            small.textContent = `${usager.ADRESSE || ""} ${ville}`.trim();

            li.appendChild(strong);
            li.appendChild(document.createElement("br"));
            li.appendChild(small);

            // Ajouter un event listener au lieu d'onclick
            li.style.cursor = "pointer";
            li.addEventListener("click", function () {
              choisirUsagerFromElement(this);
            });

            ul.appendChild(li);
          });

          listeElement.appendChild(ul);
        }
      }
    })
    .catch((error) => {
      console.error("Erreur lors de la recherche:", error);
      const listeElement = document.getElementById("listeUsagers");
      listeElement.innerHTML =
        '<ul class="usager-list"><li class="no-data">Erreur lors de la recherche</li></ul>';
    });
}

function choisirUsagerFromElement(element) {
  const numInscription = element.getAttribute("data-num-inscription");
  const civilite = element.getAttribute("data-civilite");
  const noms = element.getAttribute("data-noms");
  const ville = element.getAttribute("data-ville");

  choisirUsager(numInscription, civilite, noms, ville);
}

function choisirUsager(id, civilite, nom, codeVille) {
  document.getElementById("noms_prenoms").value = nom;
  document.getElementById("civilite").value = civilite;
  document.getElementById("code_ville").value = codeVille;
  fermerModalUsager();
}

function fermerModalUsager() {
  document.getElementById("modalUsager").style.display = "none";
  document.getElementById("rechercheUsager").value = "";
}

function selectionnerUtilisations() {
  utilisationsSelectionnees = [];

  // Trier les types d'utilisation par ordre alphabétique
  const typesTries = [...typesUtilisation].sort((a, b) => {
    return a.nom.localeCompare(b.nom, "fr", { sensitivity: "base" });
  });

  let html = '<div class="utilisations-grid">';
  typesTries.forEach((type) => {
    html += `
            <div class="utilisation-item" onclick="toggleUtilisation(${type.id})">
                <input type="checkbox" id="util_${type.id}" value="${type.code}" onchange="updateUtilisationItem(${type.id})">
                <div class="utilisation-icon-container">
                    <img src="${type.logo}" alt="${type.nom}" class="utilisation-logo" 
                         onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<span class=\\'utilisation-fallback\\'>${type.fallback || "📋"}</span>'">
                </div>
                <label for="util_${type.id}" class="utilisation-label">${type.nom}</label>
            </div>
        `;
  });
  html += "</div>";
  document.getElementById("grilleUtilisations").innerHTML = html;
  document.getElementById("modalUtilisations").style.display = "block";
}

function updateUtilisationItem(id) {
  const checkbox = document.getElementById(`util_${id}`);
  const item = checkbox.closest(".utilisation-item");
  if (checkbox.checked) {
    item.classList.add("checked");
  } else {
    item.classList.remove("checked");
  }
}

function toggleUtilisation(id) {
  const checkbox = document.getElementById(`util_${id}`);
  checkbox.checked = !checkbox.checked;
  updateUtilisationItem(id);
}

function validerUtilisations() {
  const checkboxes = document.querySelectorAll(
    '#grilleUtilisations input[type="checkbox"]:checked',
  );
  const utilisations = [];
  const tests = {};

  checkboxes.forEach((cb) => {
    const type = typesUtilisation.find((t) => t.code === cb.value);
    if (type) {
      utilisations.push(type.nom);
      tests[type.code] = "X";
    }
  });

  document.getElementById("utilisations").value = utilisations.join(", ");
  document.getElementById("tests").value = JSON.stringify(tests);
  fermerModalUtilisations();
}

function fermerModalUtilisations() {
  document.getElementById("modalUtilisations").style.display = "none";
}

function selectionnerPoste() {
  const site = document.getElementById("site").value;
  fetch(
    `api/postes.php?site=${site}&date=${new Date().toISOString().split("T")[0]}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        let html = '<div class="postes-grid">';
        for (let i = 1; i <= 8; i++) {
          const posteLibre = data.postes.find(
            (p) => p.NUMERO_POSTE === i && p.STATUT === "Libre",
          );
          const disabled = !posteLibre ? "disabled" : "";
          html += `
                        <div class="poste-item ${disabled}" onclick="choisirPoste(${i}, ${disabled ? "false" : "true"})">
                            <div class="poste-numero">${site === "BAC" ? "Poste" : "Mac"} ${i}</div>
                            <div class="poste-statut ${posteLibre ? "libre" : "occupe"}">
                                ${posteLibre ? "Libre" : "Occupé"}
                            </div>
                        </div>
                    `;
        }
        html += "</div>";
        document.getElementById("grillePostes").innerHTML = html;
        document.getElementById("modalPoste").style.display = "block";
      }
    });
}

function choisirPoste(numero, disponible) {
  if (!disponible) {
    alert("Ce poste est actuellement occupé !");
    return;
  }
  const site = document.getElementById("site").value;
  document.getElementById("postes").value =
    `${site === "BAC" ? "Poste" : "Mac"} ${String(numero).padStart(2, "0")}`;
  fermerModalPoste();
}

function fermerModalPoste() {
  document.getElementById("modalPoste").style.display = "none";
}

function selectionnerAgent() {
  fetch("api/agents.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        let html = '<ul class="agent-list">';
        data.agents.forEach((agent) => {
          html += `<li onclick="choisirAgent('${agent.NOM_AGENT}')">${agent.NOM_AGENT}</li>`;
        });
        html += "</ul>";
        document.getElementById("listeAgents").innerHTML = html;
        document.getElementById("modalAgent").style.display = "block";
      }
    });
}

function choisirAgent(nom) {
  document.getElementById("referent").value = nom;
  fermerModalAgent();
}

function fermerModalAgent() {
  document.getElementById("modalAgent").style.display = "none";
}
