
// input:
// language
// filname
// code

//output:
// severity
// file
// issue
// suggestion

let AI_review_result;

async function send_review() {
    
    //validate input
    const language = document.getElementById('language').value;
    const filname = document.getElementById('filename').value;
    const code = document.getElementById('code').value;

    if(code.trim() === ''){
        alert("Code field can't be empty.");
        return;
    }
    const payload = {
        language: language,
        filename: filname,
        code: code
    };

    const response = await fetch('../api/review.php', {
        method : 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });

    const response_data = await response.json();
    
    AI_review_result = response_data;

    const formatted_json = JSON.stringify(response_data, null, 2);
    
    document.getElementById('results').innerHTML = `<pre>${formatted_json}</pre>`;
    
}

function compare_results(){
    const human_review_string = document.getElementById("human_review_input").value;
    const comparison_output = document.getElementById('human_vs_ai_result');
    comparison_output.innerHTML = '';

    if(!AI_review_result){
        comparison_output.innerHTML = `<p class="medium">Please run the AI review first.</p>`;
        return ;
    }
    if(human_review_string.trim()===''){
         comparison_output.innerHTML = `<p class="medium">Please enter your review first.</p>`;
        return ;
    }
    let human_review_data;
    try{
        human_review_data = JSON.parse(human_review_string);

    }catch(e){
        comparison_output.innerHTML = `<p class="high">Error: Human review input is not valid JSON.</p>`;
        return;
    }

    const ai_count = Array.isArray(AI_review_result) ? AI_review_result.length : 0;
    const human_count = Array.isArray(human_review_data) ? human_review_data.length : 0;

    let html_output = "<h3>Comparison Summary</h3>";
    html_output += `<ul>`;
    html_output += `<li>AI Issues Found: <span class="low">${ai_count}</span></li>`;
    html_output += `<li>Human Issues Found: <span class="low">${human_count}</span></li>`;
    html_output += `</ul>`;

    // check difference without the use of AI API
    if (ai_count === human_count && ai_count > 0) {
        html_output += `<p class="low">The AI and Human found the same number of Issues (${ai_count}).</p>`;
    } else if (ai_count !== human_count) {
        const diff = Math.abs(ai_count - human_count);
        const source = ai_count > human_count ? 'AI' : 'Human';
        html_output += `<p class="medium">${source} found ${diff} more issues.</p>`;
    } else if (ai_count === 0 && human_count === 0) {
        html_output += `<p class="low">Neither the AI nor the Human found any issues.</p>`;
    }

    comparison_output.innerHTML = html_output;
}

// {
//   "language": "python",
//   "filename": "unsafe.py",
//   "code": "data = request.get_json(); save_to_db(data)"
// }