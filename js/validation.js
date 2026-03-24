export function validateProgramme({ name, description }) {
  if (!name || name.length < 3) {
    return "Program name must be at least 3 characters";
  }
  if(name.length>100){
        return "Program name must be at less than 100 characters";

  }

  if (!description || description.length < 10) {
    return "Description must be at least 10 characters";
  }

  if(description.length>1024){
        return "Description must be at less than 1024 characters";
  }

  return null;
}

export function validateModule({ name, program_id,description }) {
  console.log("name",name)
  console.log("program",program_id)
  console.log("description",description)
  
   if (! program_id) {
  return "Please select a programme";
}
  if (!name || name.length < 3) {
    return "Module name must be at least 3 characters";
  }

   if(name.length>100){
        return "Program name must be at less than 100 characters";

  }

    if (!description || description.length < 10) {
    return "Description must be at least 10 characters";
  }

  if(description.length>1024){
        return "Description must be at less than 1024 characters";
  }

 
  return null;
}