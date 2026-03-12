"""Sample HR policy documents for RAG demo data."""

LEAVE_POLICY = """
Company Leave Policy - Effective January 2024

1. Annual Leave
   - All full-time employees are entitled to 20 days of paid annual leave per calendar year.
   - Annual leave accrues at a rate of 1.67 days per month.
   - Unused annual leave can be carried forward up to a maximum of 5 days into the next calendar year.
   - Leave requests must be submitted at least 5 business days in advance for planned absences.
   - Management approval is required for leave periods exceeding 5 consecutive days.

2. Sick Leave
   - Employees are entitled to 12 days of paid sick leave per year.
   - A medical certificate is required for sick leave exceeding 2 consecutive days.
   - Unused sick leave does not carry forward to the next year.
   - Employees must notify their manager within 1 hour of their scheduled start time.

3. Parental Leave
   - Primary caregivers are entitled to 16 weeks of paid parental leave.
   - Secondary caregivers are entitled to 4 weeks of paid parental leave.
   - Parental leave must be taken within 12 months of the child's birth or adoption.
   - Employees must provide at least 4 weeks notice before commencing parental leave.

4. Bereavement Leave
   - 5 days of paid leave for the death of an immediate family member.
   - 2 days of paid leave for the death of an extended family member.
   - Additional unpaid leave may be granted at management's discretion.

5. Public Holidays
   - All employees are entitled to paid leave on designated public holidays.
   - Employees required to work on public holidays will receive double-time compensation.
"""

ATTENDANCE_POLICY = """
Company Attendance Policy - Effective January 2024

1. Working Hours
   - Standard working hours are 9:00 AM to 5:30 PM, Monday through Friday.
   - A 30-minute lunch break is included within working hours.
   - Total expected working hours per week: 40 hours.
   - Flexible working arrangements may be approved by department heads.

2. Check-in/Check-out
   - All employees must check in upon arrival and check out upon departure.
   - The check-in system is available via the company's attendance portal or mobile app.
   - Failure to check in/out will be recorded as an attendance exception.
   - Managers will be notified of any attendance exceptions for their team members.

3. Tardiness
   - Arriving more than 15 minutes after scheduled start time is considered tardy.
   - Three instances of tardiness in a calendar month will trigger a verbal warning.
   - Continued tardiness may result in formal disciplinary action.
   - Habitual tardiness (5+ instances per month) may affect performance reviews.

4. Remote Work
   - Eligible employees may work remotely up to 2 days per week with manager approval.
   - Remote work days must be agreed upon in advance with your direct manager.
   - Employees must be available during core hours (10:00 AM - 3:00 PM) while remote.
   - A stable internet connection and appropriate workspace are required.

5. Overtime
   - Overtime work requires prior approval from the department head.
   - Non-exempt employees are compensated at 1.5x hourly rate for overtime hours.
   - Overtime exceeding 10 hours per week requires HR department approval.
   - Compensatory time off may be offered as an alternative to overtime pay.

6. Absence Reporting
   - Unplanned absences must be reported to the direct manager within 1 hour of start time.
   - Extended absences (3+ days) require documentation and HR notification.
   - Patterns of unauthorized absence will be addressed through progressive discipline.
"""

CODE_OF_CONDUCT = """
Company Code of Conduct - Effective January 2024

1. Professional Behavior
   - All employees are expected to maintain a professional demeanor at all times.
   - Harassment, discrimination, or bullying of any kind is strictly prohibited.
   - Respectful communication is expected in all interactions, whether in-person or digital.
   - Violations should be reported to HR or through the anonymous reporting hotline.

2. Confidentiality
   - Employees must protect confidential company and client information.
   - Sharing sensitive data outside authorized channels is a terminable offense.
   - All confidential documents must be properly stored and disposed of.
   - NDAs must be signed and acknowledged upon employment commencement.

3. Conflict of Interest
   - Employees must disclose any potential conflicts of interest to their manager.
   - Outside employment or business interests must not interfere with company duties.
   - Accepting gifts or favors from vendors/clients exceeding $50 value is prohibited.
   - Financial interests in competing organizations must be disclosed to HR.

4. Use of Company Resources
   - Company equipment and resources should be used primarily for business purposes.
   - Limited personal use of company equipment is permitted but should not affect productivity.
   - Software and digital resources must be used in compliance with licensing agreements.
   - All company property must be returned upon termination of employment.

5. Health and Safety
   - All employees must comply with workplace health and safety regulations.
   - Safety hazards should be reported immediately to the facilities team.
   - Emergency procedures must be followed during drills and actual emergencies.
   - The company provides ergonomic assessments upon employee request.

6. Disciplinary Process
   - The company follows a progressive discipline approach:
     a. Verbal warning
     b. Written warning
     c. Final written warning
     d. Termination
   - Severe violations may result in immediate termination without prior warnings.
   - All disciplinary actions are documented and maintained in the employee's file.
   - Employees have the right to appeal disciplinary decisions through HR.
"""

# All documents collected for easy iteration
SAMPLE_DOCUMENTS = [
    {
        "id": "leave-policy",
        "title": "Company Leave Policy",
        "content": LEAVE_POLICY,
        "metadata": {"type": "policy", "category": "leave", "year": 2024},
    },
    {
        "id": "attendance-policy",
        "title": "Company Attendance Policy",
        "content": ATTENDANCE_POLICY,
        "metadata": {"type": "policy", "category": "attendance", "year": 2024},
    },
    {
        "id": "code-of-conduct",
        "title": "Company Code of Conduct",
        "content": CODE_OF_CONDUCT,
        "metadata": {"type": "policy", "category": "conduct", "year": 2024},
    },
]
