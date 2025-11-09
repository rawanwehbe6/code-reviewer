// function trigger_alert(){
//     alert("trigger_alert() is connected and working!");
// }

// input:
// language
// filname
// code

//output:
// severity
// file
// issue
// suggestion

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
    //validate response

    const formatted_json = JSON.stringify(response_data, null, 2);
    document.getElementById('results').innerHTML = `<pre>${formatted_json}</pre>`;

    
    //build html table to return: th=column, tr=row, td=cell
    // let result_html=`<table>
    //                     <tr>
    //                     <th>Severity</th>
    //                     <th>File</th>
    //                     <th>Issue</th>
    //                     <th>Suggestions</th>
    //                     </tr>`;

    // response_data.forEach(element => { //array
    //     result_html += `<tr>
    //         <td class="${element.severity}">${element.severity}</td>
    //         <td>${element.file}</td>
    //         <td>${element.issue}</td>
    //         <td>${element.suggestion}</td>
    //     </tr>`;
    // });
    // result_html+=`</table>`;

    // document.getElementById('results').innerHTML = result_html;
}